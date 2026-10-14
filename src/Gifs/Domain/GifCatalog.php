<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

use Prex\Gifs\Domain\Exception\GifCatalogUnavailable;
use Prex\Gifs\Domain\Exception\GifNotFound;

/**
 * Port (driven side): the external catalog of GIFs.
 *
 * GIPHY is only one possible implementation; the use cases depend on this
 * interface, never on an HTTP client.
 *
 * @throws GifCatalogUnavailable when the provider cannot be reached
 */
interface GifCatalog
{
    public function search(SearchCriteria $criteria): GifSearchResult;

    /**
     * @throws GifNotFound when the provider has no gif with that id
     */
    public function findById(GifId $id): Gif;
}
