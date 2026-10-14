<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Domain\Exception\InvalidSearchTerm;
use Prex\Gifs\Domain\SearchTerm;

final class SearchTermTest extends TestCase
{
    #[Test]
    public function it_collapses_redundant_whitespace(): void
    {
        self::assertSame('funny cat', SearchTerm::fromString("  funny   \n cat  ")->value);
    }

    #[Test]
    public function it_rejects_an_empty_term(): void
    {
        $this->expectException(InvalidSearchTerm::class);

        SearchTerm::fromString('   ');
    }

    #[Test]
    public function it_rejects_a_term_longer_than_the_maximum(): void
    {
        $this->expectException(InvalidSearchTerm::class);

        SearchTerm::fromString(str_repeat('a', SearchTerm::MAX_LENGTH + 1));
    }
}
