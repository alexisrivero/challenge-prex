<?php

declare(strict_types=1);

namespace Prex\Audit\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prex\Audit\Application\RecordServiceInteraction\RecordServiceInteractionCommand;
use Prex\Audit\Application\RecordServiceInteraction\RecordServiceInteractionHandler;
use Prex\Shared\Infrastructure\Http\AuthenticatedUser;
use Prex\Shared\Infrastructure\Http\RequestUserContext;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Primary adapter that feeds the audit trail.
 *
 * It is a *terminable* middleware: the interaction is persisted once the
 * response has been sent to the client, so auditing adds no latency to the API
 * and a failure while auditing never breaks a successful call (it is reported
 * to the logs instead).
 *
 * The caller identity travels in {@see RequestUserContext} - and not in a property of
 * this class - because Laravel resolves a fresh middleware instance to run
 * `terminate()`.
 */
final class RecordServiceInteraction
{
    public function __construct(
        private readonly RecordServiceInteractionHandler $handler,
        private readonly AuthenticatedUser $authenticatedUser,
        private readonly RequestUserContext $context,
        private readonly bool $enabled = true,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Resolved while the request is still alive: at this point the guard
        // has already authenticated the bearer token (if there was one).
        if ($this->context->userId() === null) {
            $userId = $this->authenticatedUser->idOrNull();

            if ($userId !== null) {
                $this->context->rememberUser($userId);
            }
        }

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            $this->handler->handle(new RecordServiceInteractionCommand(
                userId: $this->context->userId() ?? $this->authenticatedUser->idOrNull(),
                httpMethod: $request->getMethod(),
                path: $request->getPathInfo(),
                serviceName: $request->route()?->getName(),
                requestBody: $this->requestPayload($request),
                statusCode: $response->getStatusCode(),
                responseBody: $this->responsePayload($response),
                ipAddress: $request->ip(),
            ));
        } catch (Throwable $exception) {
            // Auditing is a cross-cutting concern: it must never turn a
            // successful response into an error for the client.
            report($exception);
        }
    }

    /**
     * @return array<array-key, mixed>
     */
    private function requestPayload(Request $request): array
    {
        // A JSON request keeps its body in the json bag and a form request in
        // the post bag; merging all three only duplicated the body.
        return [
            ...$request->query->all(),
            ...($request->isJson() ? (array) $request->json()->all() : $request->post()),
        ];
    }

    private function responsePayload(Response $response): mixed
    {
        $content = $response->getContent();

        return $content === false ? null : $content;
    }
}
