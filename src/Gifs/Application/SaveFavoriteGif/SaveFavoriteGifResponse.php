<?php

declare(strict_types=1);

namespace Prex\Gifs\Application\SaveFavoriteGif;

use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\Gif;

final readonly class SaveFavoriteGifResponse
{
    public function __construct(
        public FavoriteGif $favorite,
        public Gif $gif,
    ) {}
}
