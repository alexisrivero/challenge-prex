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

    /**
     * Spends the same work as a real verification, and throws the result away.
     *
     * Called when the email does not exist: without it the answer comes back an
     * order of magnitude faster than for a registered email, and the response
     * time alone tells an attacker which accounts exist.
     */
    public function verifyDecoy(PlainPassword $password): void;
}
