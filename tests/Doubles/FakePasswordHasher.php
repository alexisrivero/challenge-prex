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

    private int $decoyVerifications = 0;

    public function hash(PlainPassword $password): HashedPassword
    {
        return HashedPassword::fromHash(self::PREFIX.$password->value());
    }

    public function matches(PlainPassword $password, HashedPassword $hashedPassword): bool
    {
        return $this->hash($password)->value() === $hashedPassword->value();
    }

    public function verifyDecoy(PlainPassword $password): void
    {
        $this->decoyVerifications++;
    }

    /**
     * Lets a test assert that the unknown email branch did the same work as the
     * wrong password one, without measuring time.
     */
    public function decoyVerifications(): int
    {
        return $this->decoyVerifications;
    }
}
