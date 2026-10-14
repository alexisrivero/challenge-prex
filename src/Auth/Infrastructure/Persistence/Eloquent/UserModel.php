<?php

declare(strict_types=1);

namespace Prex\Auth\Infrastructure\Persistence\Eloquent;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Eloquent persistence model for users.
 *
 * It is deliberately an *infrastructure* detail: the use cases never see it,
 * they only talk to the UserRepository port which returns domain entities.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
final class UserModel extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = ['name', 'email', 'password'];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
