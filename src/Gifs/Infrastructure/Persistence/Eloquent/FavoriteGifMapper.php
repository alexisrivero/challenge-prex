<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Persistence\Eloquent;

use DateTimeImmutable;
use DateTimeZone;
use Prex\Gifs\Domain\Favorite\Alias;
use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\Favorite\FavoriteGifId;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\ValueObject\UserId;

final readonly class FavoriteGifMapper
{
    public function toDomain(FavoriteGifModel $model): FavoriteGif
    {
        return new FavoriteGif(
            id: FavoriteGifId::fromString((string) $model->id),
            userId: UserId::fromInt((int) $model->user_id),
            gifId: GifId::fromString((string) $model->gif_id),
            alias: Alias::fromString((string) $model->alias),
            savedAt: new DateTimeImmutable(
                $model->saved_at->toDateTimeString(),
                new DateTimeZone('UTC')
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(FavoriteGif $favorite): array
    {
        return [
            'id' => $favorite->id->value,
            'user_id' => $favorite->userId->value,
            'gif_id' => $favorite->gifId->value,
            'alias' => $favorite->alias->value,
            'saved_at' => $favorite->savedAt->format('Y-m-d H:i:s'),
        ];
    }
}
