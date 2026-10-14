<?php

declare(strict_types=1);

namespace Prex\Gifs\Application\FindGif;

use Prex\Gifs\Domain\Gif;
use Prex\Gifs\Domain\GifCatalog;
use Prex\Gifs\Domain\GifId;

/**
 * Use case: get the information of one specific GIF.
 */
final readonly class FindGifHandler
{
    public function __construct(private GifCatalog $catalog) {}

    public function handle(FindGifQuery $query): Gif
    {
        return $this->catalog->findById(GifId::fromString($query->gifId));
    }
}
