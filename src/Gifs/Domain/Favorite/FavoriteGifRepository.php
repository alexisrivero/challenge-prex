<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Favorite;

use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Port (driven side): persistence of the FavoriteGif aggregate.
 */
interface FavoriteGifRepository
{
    public function save(FavoriteGif $favorite): void;

    public function findByUserAndGif(UserId $userId, GifId $gifId): ?FavoriteGif;

    /**
     * @return list<FavoriteGif>
     */
    public function findByUser(UserId $userId): array;
}
