<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Security;

use Illuminate\Contracts\Hashing\Hasher;
use Prex\Auth\Domain\HashedPassword;
use Prex\Auth\Domain\PasswordHasher;
use Prex\Auth\Domain\PlainPassword;

/**
 * Adapter for the PasswordHasher port backed by Laravel's hashing component
 * (bcrypt/argon2 depending on configuration).
 */
final readonly class LaravelPasswordHasher implements PasswordHasher
{
    public function __construct(private Hasher $hasher) {}

    public function hash(PlainPassword $password): HashedPassword
    {
        return HashedPassword::fromHash($this->hasher->make($password->value()));
    }

    public function matches(PlainPassword $password, HashedPassword $hashedPassword): bool
    {
        return $this->hasher->check($password->value(), $hashedPassword->value());
    }
}
