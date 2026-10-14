<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Aggregate root of the Auth context.
 */
final readonly class User
{
    public function __construct(
        public UserId $id,
        public string $name,
        public Email $email,
        private HashedPassword $hashedPassword,
    ) {}

    public function hashedPassword(): HashedPassword
    {
        return $this->hashedPassword;
    }

    /**
     * Credential check expressed in the domain: the rule ("a user authenticates
     * when the given password matches the stored hash") lives here, while the
     * hashing algorithm stays behind a port.
     */
    public function authenticate(PlainPassword $password, PasswordHasher $hasher): bool
    {
        return $hasher->matches($password, $this->hashedPassword);
    }
}
