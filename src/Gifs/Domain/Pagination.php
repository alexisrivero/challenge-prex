<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

use Prex\Gifs\Domain\Exception\InvalidPagination;

/**
 * Offset based pagination, with the boundaries accepted by the GIPHY search
 * endpoint (max. 50 results per page, max. 4999 offset).
 */
final readonly class Pagination
{
    public const int DEFAULT_LIMIT = 25;

    public const int MAX_LIMIT = 50;

    public const int MAX_OFFSET = 4999;

    private function __construct(public int $limit, public int $offset) {}

    public static function of(?int $limit, ?int $offset): self
    {
        $limit ??= self::DEFAULT_LIMIT;
        $offset ??= 0;

        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            throw InvalidPagination::limitOutOfRange($limit);
        }

        if ($offset < 0 || $offset > self::MAX_OFFSET) {
            throw InvalidPagination::offsetOutOfRange($offset);
        }

        return new self($limit, $offset);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_LIMIT, 0);
    }
}
