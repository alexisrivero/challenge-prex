<?php

declare(strict_types=1);

namespace Tests\Doubles;

use Prex\Auth\Domain\Email;
use Prex\Auth\Domain\User;
use Prex\Auth\Domain\UserRepository;
use Prex\Shared\Domain\ValueObject\UserId;

final class InMemoryUserRepository implements UserRepository
{
    /**
     * @param  list<User>  $users
     */
    public function __construct(private array $users = []) {}

    public function add(User $user): void
    {
        $this->users[] = $user;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email->equals($email)) {
                return $user;
            }
        }

        return null;
    }

    public function findById(UserId $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->id->equals($id)) {
                return $user;
            }
        }

        return null;
    }
}
