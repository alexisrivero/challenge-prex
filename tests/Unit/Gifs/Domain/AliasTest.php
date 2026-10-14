<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Domain\Favorite\Alias;
use Prex\Gifs\Domain\Favorite\Exception\InvalidAlias;

final class AliasTest extends TestCase
{
    #[Test]
    public function it_normalises_whitespace(): void
    {
        self::assertSame('my favourite cat', Alias::fromString('  my   favourite  cat ')->value);
    }

    #[Test]
    public function it_rejects_an_empty_alias(): void
    {
        $this->expectException(InvalidAlias::class);

        Alias::fromString('  ');
    }

    #[Test]
    public function it_rejects_an_alias_longer_than_the_maximum(): void
    {
        $this->expectException(InvalidAlias::class);

        Alias::fromString(str_repeat('a', Alias::MAX_LENGTH + 1));
    }
}
