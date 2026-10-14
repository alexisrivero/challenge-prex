<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Domain\Favorite\Alias;
use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\ValueObject\UserId;
use Prex\Shared\Domain\ValueObject\Uuid;

final class FavoriteGifTest extends TestCase
{
    #[Test]
    public function it_generates_its_own_identity(): void
    {
        $favorite = FavoriteGif::save(
            UserId::fromInt(1),
            GifId::fromString('abc123'),
            Alias::fromString('my gif'),
            new DateTimeImmutable('2026-03-05 10:00:00'),
        );

        self::assertTrue(Uuid::isValid($favorite->id->value));
    }

    #[Test]
    public function two_favorites_never_share_an_identity(): void
    {
        $first = FavoriteGif::save(
            UserId::fromInt(1),
            GifId::fromString('abc123'),
            Alias::fromString('one'),
            new DateTimeImmutable,
        );

        $second = FavoriteGif::save(
            UserId::fromInt(1),
            GifId::fromString('abc124'),
            Alias::fromString('two'),
            new DateTimeImmutable,
        );

        self::assertNotSame($first->id->value, $second->id->value);
    }

    #[Test]
    public function it_knows_which_user_owns_it(): void
    {
        $favorite = FavoriteGif::save(
            UserId::fromInt(42),
            GifId::fromString('abc123'),
            Alias::fromString('mine'),
            new DateTimeImmutable,
        );

        self::assertTrue($favorite->belongsTo(UserId::fromInt(42)));
        self::assertFalse($favorite->belongsTo(UserId::fromInt(43)));
    }
}
