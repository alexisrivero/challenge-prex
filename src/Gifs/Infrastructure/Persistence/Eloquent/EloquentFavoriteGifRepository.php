<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\UniqueConstraintViolationException;
use Prex\Gifs\Domain\Favorite\Exception\FavoriteGifAlreadySaved;
use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\Favorite\FavoriteGifRepository;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\ValueObject\UserId;

final readonly class EloquentFavoriteGifRepository implements FavoriteGifRepository
{
    public function __construct(private FavoriteGifMapper $mapper) {}

    /**
     * The `(user_id, gif_id)` unique index is the only authority on duplicates:
     * checking before inserting cannot be atomic, so two concurrent requests
     * both pass the check and one of them hits the index. Translating the
     * violation here keeps that race answering `409` instead of `500`.
     */
    public function save(FavoriteGif $favorite): void
    {
        try {
            FavoriteGifModel::query()->create($this->mapper->toAttributes($favorite));
        } catch (UniqueConstraintViolationException) {
            throw FavoriteGifAlreadySaved::create($favorite->userId, $favorite->gifId);
        }
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
