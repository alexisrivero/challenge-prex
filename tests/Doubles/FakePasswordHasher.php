<?php

declare(strict_types=1);

namespace Tests\Doubles;

use Prex\Auth\Domain\HashedPassword;
use Prex\Auth\Domain\PasswordHasher;
use Prex\Auth\Domain\PlainPassword;

/**
 * Reversible "hashing" so tests stay fast and readable.
 */
final class FakePasswordHasher implements PasswordHasher
{
    private const string PREFIX = 'hashed:';

    public function hash(PlainPassword $password): HashedPassword
    {
        return HashedPassword::fromHash(self::PREFIX.$password->value());
    }

    public function matches(PlainPassword $password, HashedPassword $hashedPassword): bool
    {
        return $this->hash($password)->value() === $hashedPassword->value();
    }
}
