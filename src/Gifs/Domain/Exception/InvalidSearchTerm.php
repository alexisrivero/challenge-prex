<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Exception;

use Prex\Gifs\Domain\SearchTerm;
use Prex\Shared\Domain\Exception\DomainException;

final class InvalidSearchTerm extends DomainException
{
    public static function empty(): self
    {
        return new self('The search term cannot be empty.');
    }

    public static function tooLong(string $value): self
    {
        return new self(sprintf(
            'The search term cannot be longer than %d characters, %d given.',
            SearchTerm::MAX_LENGTH,
            mb_strlen($value)
        ));
    }

    public function errorCode(): string
    {
        return 'invalid_search_term';
    }
}
