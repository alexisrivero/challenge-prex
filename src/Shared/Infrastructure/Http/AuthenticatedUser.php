<?php

declare(strict_types=1);

namespace Prex\Shared\Infrastructure\Http;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

/**
 * Gives the primary adapters access to the identity behind the access token
 * without spreading `auth()` helpers all over the code base.
 */
final readonly class AuthenticatedUser
{
    public function __construct(
        private AuthFactory $auth,
        private string $guard = 'api',
    ) {}

    /**
     * @throws AuthenticationException when there is no authenticated user
     */
    public function id(): int
    {
        $id = $this->idOrNull();

        if ($id === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $id;
    }

    public function idOrNull(): ?int
    {
        $id = $this->auth->guard($this->guard)->id();

        return is_numeric($id) ? (int) $id : null;
    }
}
