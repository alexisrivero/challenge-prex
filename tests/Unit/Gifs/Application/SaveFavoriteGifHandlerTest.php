<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Application\SaveFavoriteGif\SaveFavoriteGifCommand;
use Prex\Gifs\Application\SaveFavoriteGif\SaveFavoriteGifHandler;
use Prex\Gifs\Domain\Exception\GifNotFound;
use Prex\Gifs\Domain\Favorite\Exception\CannotSaveFavoriteForAnotherUser;
use Prex\Gifs\Domain\Favorite\Exception\FavoriteGifAlreadySaved;
use Prex\Gifs\Domain\Favorite\Exception\InvalidAlias;
use Tests\Doubles\FakeGifCatalog;
use Tests\Doubles\FixedClock;
use Tests\Doubles\InMemoryFavoriteGifRepository;

final class SaveFavoriteGifHandlerTest extends TestCase
{
    private InMemoryFavoriteGifRepository $favorites;

    private FakeGifCatalog $catalog;

    private SaveFavoriteGifHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->favorites = new InMemoryFavoriteGifRepository;
        $this->catalog = FakeGifCatalog::withGifs(FakeGifCatalog::gif('gif-1', 'A funny cat'));

        $this->handler = new SaveFavoriteGifHandler(
            $this->favorites,
            $this->catalog,
            new FixedClock('2026-03-05 10:00:00'),
        );
    }

    #[Test]
    public function it_saves_the_favorite_for_the_authenticated_user(): void
    {
        $response = $this->handler->handle(new SaveFavoriteGifCommand(
            gifId: 'gif-1',
            alias: 'my cat',
            userId: 7,
            authenticatedUserId: 7,
        ));

        self::assertCount(1, $this->favorites->saved);
        self::assertSame('gif-1', $response->favorite->gifId->value);
        self::assertSame('my cat', $response->favorite->alias->value);
        self::assertSame(7, $response->favorite->userId->value);
        self::assertSame('2026-03-05 10:00:00', $response->favorite->savedAt->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_returns_the_gif_data_together_with_the_favorite(): void
    {
        $response = $this->handler->handle(new SaveFavoriteGifCommand('gif-1', 'my cat', 7, 7));

        self::assertSame('A funny cat', $response->gif->title);
    }

    #[Test]
    public function a_user_cannot_save_favorites_for_somebody_else(): void
    {
        try {
            $this->handler->handle(new SaveFavoriteGifCommand('gif-1', 'my cat', userId: 8, authenticatedUserId: 7));
            self::fail('A CannotSaveFavoriteForAnotherUser exception was expected.');
        } catch (CannotSaveFavoriteForAnotherUser $exception) {
            self::assertSame('favorite_user_mismatch', $exception->errorCode());
        }

        self::assertSame([], $this->favorites->saved);
    }

    #[Test]
    public function the_same_gif_cannot_be_saved_twice_by_the_same_user(): void
    {
        $this->handler->handle(new SaveFavoriteGifCommand('gif-1', 'my cat', 7, 7));

        $this->expectException(FavoriteGifAlreadySaved::class);

        $this->handler->handle(new SaveFavoriteGifCommand('gif-1', 'the same cat', 7, 7));
    }

    #[Test]
    public function two_users_can_save_the_same_gif(): void
    {
        $this->handler->handle(new SaveFavoriteGifCommand('gif-1', 'my cat', 7, 7));
        $this->handler->handle(new SaveFavoriteGifCommand('gif-1', 'my cat too', 8, 8));

        self::assertCount(2, $this->favorites->saved);
    }

    #[Test]
    public function it_refuses_to_save_a_gif_that_does_not_exist_in_the_catalog(): void
    {
        $this->expectException(GifNotFound::class);

        $this->handler->handle(new SaveFavoriteGifCommand('unknown-gif', 'my cat', 7, 7));
    }

    #[Test]
    public function it_rejects_an_empty_alias(): void
    {
        $this->expectException(InvalidAlias::class);

        $this->handler->handle(new SaveFavoriteGifCommand('gif-1', '   ', 7, 7));
    }
}
