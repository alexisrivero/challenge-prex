<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Persistence\Eloquent;

use Prex\Auth\Domain\Email;
use Prex\Auth\Domain\User;
use Prex\Auth\Domain\UserRepository;
use Prex\Shared\Domain\ValueObject\UserId;

final readonly class EloquentUserRepository implements UserRepository
{
    public function __construct(private UserMapper $mapper) {}

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::query()->where('email', $email->value)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::query()->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
