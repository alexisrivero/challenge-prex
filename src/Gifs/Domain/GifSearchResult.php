<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

use Countable;

/**
 * Result of a catalog search: the page of GIFs plus the pagination metadata
 * reported by the provider.
 */
final readonly class GifSearchResult implements Countable
{
    /**
     * @param  list<Gif>  $gifs
     */
    public function __construct(
        public array $gifs,
        public Pagination $pagination,
        public int $totalCount,
    ) {}

    public static function empty(Pagination $pagination): self
    {
        return new self([], $pagination, 0);
    }

    public function count(): int
    {
        return count($this->gifs);
    }

    public function isEmpty(): bool
    {
        return $this->gifs === [];
    }
}
