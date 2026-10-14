<?php

declare(strict_types=1);

namespace Prex\Shared\Infrastructure\Http\Exception;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Prex\Shared\Domain\Exception\Conflict;
use Prex\Shared\Domain\Exception\DomainException;
use Prex\Shared\Domain\Exception\Forbidden;
use Prex\Shared\Domain\Exception\NotFound;
use Prex\Shared\Domain\Exception\ServiceUnavailable;
use Prex\Shared\Domain\Exception\Unauthorized;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Single place where errors become HTTP responses.
 *
 * The domain layer never mentions status codes: it throws exceptions that
 * either implement one of the marker interfaces of the shared kernel or are
 * plain invariant violations (422). This adapter performs the translation and
 * keeps one single error envelope for the whole API:
 *
 *     { "error": { "code": "...", "message": "...", "context": { ... } } }
 */
final readonly class ApiExceptionRenderer
{
    public function __construct(private bool $debug = false) {}

    public function render(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! $request->expectsJson() && ! $request->is('api/*')) {
            return null;
        }

        return match (true) {
            $exception instanceof ValidationException => $this->response(
                'validation_failed',
                'The given data was invalid.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['errors' => $exception->errors()],
            ),
            $exception instanceof DomainException => $this->fromDomain($exception),
            $exception instanceof AuthenticationException => $this->response(
                'unauthenticated',
                'An active access token is required to consume this service.',
                Response::HTTP_UNAUTHORIZED,
            ),
            $exception instanceof AuthorizationException => $this->response(
                'forbidden',
                $exception->getMessage() !== '' ? $exception->getMessage() : 'This action is unauthorized.',
                Response::HTTP_FORBIDDEN,
            ),
            $exception instanceof ModelNotFoundException => $this->response(
                'resource_not_found',
                'The requested resource does not exist.',
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof HttpExceptionInterface => $this->fromHttpException($exception),
            default => $this->unexpected($exception),
        };
    }

    private function fromDomain(DomainException $exception): JsonResponse
    {
        $status = match (true) {
            $exception instanceof Unauthorized => Response::HTTP_UNAUTHORIZED,
            $exception instanceof Forbidden => Response::HTTP_FORBIDDEN,
            $exception instanceof NotFound => Response::HTTP_NOT_FOUND,
            $exception instanceof Conflict => Response::HTTP_CONFLICT,
            $exception instanceof ServiceUnavailable => Response::HTTP_SERVICE_UNAVAILABLE,
            // An invariant of the domain was violated by the input.
            default => Response::HTTP_UNPROCESSABLE_ENTITY,
        };

        return $this->response(
            $exception->errorCode(),
            $exception->getMessage(),
            $status,
            $exception->context() === [] ? [] : ['context' => $exception->context()],
        );
    }

    private function fromHttpException(HttpExceptionInterface&Throwable $exception): JsonResponse
    {
        $status = $exception->getStatusCode();

        $code = match ($status) {
            Response::HTTP_NOT_FOUND => 'endpoint_not_found',
            Response::HTTP_METHOD_NOT_ALLOWED => 'method_not_allowed',
            Response::HTTP_TOO_MANY_REQUESTS => 'too_many_requests',
            default => 'http_error',
        };

        $message = $exception->getMessage() !== ''
            ? $exception->getMessage()
            : (Response::$statusTexts[$status] ?? 'HTTP error');

        return $this->response($code, $message, $status)
            ->withHeaders($exception->getHeaders());
    }

    private function unexpected(Throwable $exception): JsonResponse
    {
        report($exception);

        return $this->response(
            'internal_server_error',
            $this->debug ? $exception->getMessage() : 'An unexpected error occurred.',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->debug ? ['exception' => $exception::class] : [],
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function response(string $code, string $message, int $status, array $extra = []): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'code' => $code,
                'message' => $message,
                ...$extra,
            ],
        ], $status);
    }
}
