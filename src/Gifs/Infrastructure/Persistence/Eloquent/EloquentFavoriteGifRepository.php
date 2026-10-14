<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Persistence\Eloquent;

use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\Favorite\FavoriteGifRepository;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\ValueObject\UserId;

final readonly class EloquentFavoriteGifRepository implements FavoriteGifRepository
{
    public function __construct(private FavoriteGifMapper $mapper) {}

    public function save(FavoriteGif $favorite): void
    {
        FavoriteGifModel::query()->updateOrCreate(
            ['id' => $favorite->id->value],
            $this->mapper->toAttributes($favorite),
        );
    }

    public function findByUserAndGif(UserId $userId, GifId $gifId): ?FavoriteGif
    {
        $model = FavoriteGifModel::query()
            ->where('user_id', $userId->value)
            ->where('gif_id', $gifId->value)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    /**
     * @return list<FavoriteGif>
     */
    public function findByUser(UserId $userId): array
    {
        return FavoriteGifModel::query()
            ->where('user_id', $userId->value)
            ->orderByDesc('saved_at')
            ->get()
            ->map(fn (FavoriteGifModel $model): FavoriteGif => $this->mapper->toDomain($model))
            ->values()
            ->all();
    }
}
