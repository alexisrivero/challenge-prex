<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Favorite\Exception;

use Prex\Shared\Domain\Exception\DomainException;
use Prex\Shared\Domain\Exception\Forbidden;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * The challenge asks for USER_ID as part of the input of "save favorite gif",
 * but the service is authenticated: accepting an arbitrary user id would let
 * anyone write into somebody else's collection. The rule enforced here is that
 * the authenticated user can only manage their own favorites.
 */
final class CannotSaveFavoriteForAnotherUser extends DomainException implements Forbidden
{
    public static function create(UserId $authenticated, UserId $target): self
    {
        return new self(sprintf(
            'User %d cannot save favorites on behalf of user %d.',
            $authenticated->value,
            $target->value
        ));
    }

    public function errorCode(): string
    {
        return 'favorite_user_mismatch';
    }
}
