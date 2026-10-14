<?php

declare(strict_types=1);

namespace Prex\Shared\Infrastructure\Http;

/**
 * Request scoped holder for "which user is this request about".
 *
 * {@see AuthenticatedUser} answers "who is calling" by asking the guard, which
 * only works once a bearer token has been validated. The login service is the
 * exception: while it runs there is no token yet, but after verifying the
 * credentials we do know the user - and the audit trail wants to know it too.
 *
 * It lives in the shared kernel because it is a cross-cutting technical
 * concern: the Auth context writes it and the Audit context reads it, without
 * either of them depending on the other.
 *
 * Registered as a singleton so the terminable middleware - resolved again after
 * the response has been sent - sees the same instance.
 */
final class RequestUserContext
{
    private ?int $userId = null;

    public function rememberUser(int $userId): void
    {
        $this->userId = $userId;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }
}
