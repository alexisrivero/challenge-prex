<?php

declare(strict_types=1);

namespace Prex\Auth\Application\Login;

use DateTimeImmutable;

/**
 * Output of the Login use case.
 */
final readonly class LoginResponse
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public DateTimeImmutable $expiresAt,
        public int $userId,
        public string $userName,
        public string $userEmail,
    ) {}
}
