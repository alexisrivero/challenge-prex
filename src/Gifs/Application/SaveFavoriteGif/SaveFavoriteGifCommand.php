<?php

declare(strict_types=1);

namespace Prex\Gifs\Application\SaveFavoriteGif;

final readonly class SaveFavoriteGifCommand
{
    public function __construct(
        public string $gifId,
        public string $alias,
        public int $userId,
        /** Id of the user performing the call, taken from the access token. */
        public int $authenticatedUserId,
    ) {}
}
