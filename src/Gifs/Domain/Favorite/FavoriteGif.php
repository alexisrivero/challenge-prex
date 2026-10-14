<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Favorite;

use DateTimeImmutable;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Aggregate root: the association between a user and a GIF of the catalog.
 */
final readonly class FavoriteGif
{
    public function __construct(
        public FavoriteGifId $id,
        public UserId $userId,
        public GifId $gifId,
        public Alias $alias,
        public DateTimeImmutable $savedAt,
    ) {}

    /**
     * Named constructor used when a user saves a new favorite.
     */
    public static function save(
        UserId $userId,
        GifId $gifId,
        Alias $alias,
        DateTimeImmutable $savedAt,
    ): self {
        return new self(FavoriteGifId::generate(), $userId, $gifId, $alias, $savedAt);
    }

    public function belongsTo(UserId $userId): bool
    {
        return $this->userId->equals($userId);
    }
}
