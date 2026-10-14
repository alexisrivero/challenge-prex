<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

/**
 * Everything the catalog needs to run a search.
 */
final readonly class SearchCriteria
{
    public function __construct(
        public SearchTerm $term,
        public Pagination $pagination,
    ) {}
}
