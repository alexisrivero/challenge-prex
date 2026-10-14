<?php

declare(strict_types=1);

namespace Tests\Doubles;

use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\Favorite\FavoriteGifRepository;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\ValueObject\UserId;

final class InMemoryFavoriteGifRepository implements FavoriteGifRepository
{
    /** @var list<FavoriteGif> */
    public array $saved = [];

    public function save(FavoriteGif $favorite): void
    {
        $this->saved[] = $favorite;
    }

    public function findByUserAndGif(UserId $userId, GifId $gifId): ?FavoriteGif
    {
        foreach ($this->saved as $favorite) {
            if ($favorite->belongsTo($userId) && $favorite->gifId->equals($gifId)) {
                return $favorite;
            }
        }

        return null;
    }

    /**
     * @return list<FavoriteGif>
     */
    public function findByUser(UserId $userId): array
    {
        return array_values(array_filter(
            $this->saved,
            fn (FavoriteGif $favorite): bool => $favorite->belongsTo($userId)
        ));
    }
}
