<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Exception;

use Prex\Gifs\Domain\Pagination;
use Prex\Shared\Domain\Exception\DomainException;

final class InvalidPagination extends DomainException
{
    public static function limitOutOfRange(int $limit): self
    {
        return new self(sprintf(
            'The limit must be between 1 and %d, %d given.',
            Pagination::MAX_LIMIT,
            $limit
        ));
    }

    public static function offsetOutOfRange(int $offset): self
    {
        return new self(sprintf(
            'The offset must be between 0 and %d, %d given.',
            Pagination::MAX_OFFSET,
            $offset
        ));
    }

    public function errorCode(): string
    {
        return 'invalid_pagination';
    }
}
