<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

/**
 * Port: hashing strategy used to store and verify credentials.
 */
interface PasswordHasher
{
    public function hash(PlainPassword $password): HashedPassword;

    public function matches(PlainPassword $password, HashedPassword $hashedPassword): bool;
}
