<?php

declare(strict_types=1);

namespace Prex\Gifs\Application\SearchGifs;

final readonly class SearchGifsQuery
{
    public function __construct(
        public string $term,
        public ?int $limit = null,
        public ?int $offset = null,
    ) {}
}
