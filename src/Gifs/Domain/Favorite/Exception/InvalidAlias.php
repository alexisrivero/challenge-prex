<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Favorite\Exception;

use Prex\Gifs\Domain\Favorite\Alias;
use Prex\Shared\Domain\Exception\DomainException;

final class InvalidAlias extends DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf(
            'The alias must be between %d and %d characters, "%s" given.',
            Alias::MIN_LENGTH,
            Alias::MAX_LENGTH,
            $value
        ));
    }

    public function errorCode(): string
    {
        return 'invalid_alias';
    }
}
