<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Persistence\Eloquent;

use Prex\Auth\Domain\Email;
use Prex\Auth\Domain\HashedPassword;
use Prex\Auth\Domain\User;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Translates between the persistence model and the domain aggregate.
 */
final readonly class UserMapper
{
    public function toDomain(UserModel $model): User
    {
        return new User(
            id: UserId::fromInt((int) $model->id),
            name: (string) $model->name,
            email: Email::fromString((string) $model->email),
            hashedPassword: HashedPassword::fromHash((string) $model->password),
        );
    }
}
