<?php

declare(strict_types=1);

namespace Prex\Gifs\Application\SearchGifs;

use Prex\Gifs\Domain\GifCatalog;
use Prex\Gifs\Domain\GifSearchResult;
use Prex\Gifs\Domain\Pagination;
use Prex\Gifs\Domain\SearchCriteria;
use Prex\Gifs\Domain\SearchTerm;

/**
 * Use case: search GIFs by phrase or term.
 */
final readonly class SearchGifsHandler
{
    public function __construct(private GifCatalog $catalog) {}

    public function handle(SearchGifsQuery $query): GifSearchResult
    {
        return $this->catalog->search(new SearchCriteria(
            term: SearchTerm::fromString($query->term),
            pagination: Pagination::of($query->limit, $query->offset),
        ));
    }
}
