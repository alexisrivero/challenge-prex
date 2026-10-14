<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Http\Controller;

use Illuminate\Http\JsonResponse;
use Prex\Auth\Application\Login\LoginHandler;
use Prex\Auth\Infrastructure\Http\Request\LoginRequest;
use Prex\Auth\Infrastructure\Http\Resource\AccessTokenResource;
use Prex\Shared\Infrastructure\Http\RequestUserContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Primary adapter (driving side): turns an HTTP request into a use case call.
 *
 * Note how thin it is - no business rule, no persistence access.
 */
final readonly class LoginController
{
    public function __construct(
        private LoginHandler $handler,
        private RequestUserContext $requestUserContext,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $response = $this->handler->handle($request->toCommand());

        // The caller is not authenticated during login, so we tell the audit
        // trail which user this interaction belongs to.
        $this->requestUserContext->rememberUser($response->userId);

        return AccessTokenResource::make($response)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
