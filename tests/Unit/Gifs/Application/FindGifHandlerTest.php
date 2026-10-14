<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Application\FindGif\FindGifHandler;
use Prex\Gifs\Application\FindGif\FindGifQuery;
use Prex\Gifs\Domain\Exception\GifNotFound;
use Prex\Gifs\Domain\Exception\InvalidGifId;
use Tests\Doubles\FakeGifCatalog;

final class FindGifHandlerTest extends TestCase
{
    #[Test]
    public function it_returns_the_requested_gif(): void
    {
        $handler = new FindGifHandler(FakeGifCatalog::withGifs(FakeGifCatalog::gif('gif-1', 'A funny cat')));

        $gif = $handler->handle(new FindGifQuery('gif-1'));

        self::assertSame('gif-1', $gif->id->value);
        self::assertSame('A funny cat', $gif->title);
        self::assertSame('https://giphy.com/gifs/gif-1', $gif->url);
    }

    #[Test]
    public function it_fails_when_the_gif_does_not_exist(): void
    {
        $handler = new FindGifHandler(FakeGifCatalog::withGifs());

        try {
            $handler->handle(new FindGifQuery('missing'));
            self::fail('A GifNotFound exception was expected.');
        } catch (GifNotFound $exception) {
            self::assertSame('gif_not_found', $exception->errorCode());
            self::assertSame(['gif_id' => 'missing'], $exception->context());
        }
    }

    #[Test]
    public function it_rejects_a_malformed_id(): void
    {
        $handler = new FindGifHandler(FakeGifCatalog::withGifs());

        $this->expectException(InvalidGifId::class);

        $handler->handle(new FindGifQuery('not valid/id'));
    }
}
