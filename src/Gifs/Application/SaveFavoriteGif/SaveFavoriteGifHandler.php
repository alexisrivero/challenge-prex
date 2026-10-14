<?php

declare(strict_types=1);

namespace Prex\Gifs\Application\SaveFavoriteGif;

use Prex\Gifs\Domain\Favorite\Alias;
use Prex\Gifs\Domain\Favorite\Exception\CannotSaveFavoriteForAnotherUser;
use Prex\Gifs\Domain\Favorite\Exception\FavoriteGifAlreadySaved;
use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\Favorite\FavoriteGifRepository;
use Prex\Gifs\Domain\GifCatalog;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\Clock;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Use case: store a GIF as favorite for a user.
 *
 * Rules applied, in order:
 *  1. a user may only write in their own collection;
 *  2. the gif must exist in the catalog (no dangling favorites);
 *  3. the same gif cannot be saved twice by the same user.
 */
final readonly class SaveFavoriteGifHandler
{
    public function __construct(
        private FavoriteGifRepository $favorites,
        private GifCatalog $catalog,
        private Clock $clock,
    ) {}

    public function handle(SaveFavoriteGifCommand $command): SaveFavoriteGifResponse
    {
        $userId = UserId::fromInt($command->userId);
        $authenticatedUserId = UserId::fromInt($command->authenticatedUserId);

        if (! $userId->equals($authenticatedUserId)) {
            throw CannotSaveFavoriteForAnotherUser::create($authenticatedUserId, $userId);
        }

        $gifId = GifId::fromString($command->gifId);
        $alias = Alias::fromString($command->alias);

        $gif = $this->catalog->findById($gifId);

        if ($this->favorites->findByUserAndGif($userId, $gifId) !== null) {
            throw FavoriteGifAlreadySaved::create($userId, $gifId);
        }

        $favorite = FavoriteGif::save($userId, $gifId, $alias, $this->clock->now());

        $this->favorites->save($favorite);

        return new SaveFavoriteGifResponse($favorite, $gif);
    }
}
