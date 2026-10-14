<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Favorite\Exception;

use Prex\Shared\Domain\Exception\DomainException;

final class InvalidFavoriteGifId extends DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('"%s" is not a valid favorite id (UUID expected).', $value));
    }

    public function errorCode(): string
    {
        return 'invalid_favorite_id';
    }
}
