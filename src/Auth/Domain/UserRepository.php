<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Port: persistence of the User aggregate.
 */
interface UserRepository
{
    public function findByEmail(Email $email): ?User;

    public function findById(UserId $id): ?User;
}
