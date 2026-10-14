<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Domain\Exception\InvalidPagination;
use Prex\Gifs\Domain\Pagination;

final class PaginationTest extends TestCase
{
    #[Test]
    public function limit_and_offset_are_optional(): void
    {
        $pagination = Pagination::of(null, null);

        self::assertSame(Pagination::DEFAULT_LIMIT, $pagination->limit);
        self::assertSame(0, $pagination->offset);
    }

    #[Test]
    public function it_keeps_the_requested_page(): void
    {
        $pagination = Pagination::of(10, 20);

        self::assertSame(10, $pagination->limit);
        self::assertSame(20, $pagination->offset);
    }

    #[Test]
    public function it_rejects_a_limit_above_the_provider_maximum(): void
    {
        $this->expectException(InvalidPagination::class);

        Pagination::of(Pagination::MAX_LIMIT + 1, 0);
    }

    #[Test]
    public function it_rejects_a_limit_below_one(): void
    {
        $this->expectException(InvalidPagination::class);

        Pagination::of(0, 0);
    }

    #[Test]
    public function it_rejects_a_negative_offset(): void
    {
        $this->expectException(InvalidPagination::class);

        Pagination::of(10, -1);
    }

    #[Test]
    public function it_rejects_an_offset_above_the_provider_maximum(): void
    {
        $this->expectException(InvalidPagination::class);

        Pagination::of(10, Pagination::MAX_OFFSET + 1);
    }
}
