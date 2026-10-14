<?php

declare(strict_types=1);

namespace Tests\Unit\Gifs\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Gifs\Application\SearchGifs\SearchGifsHandler;
use Prex\Gifs\Application\SearchGifs\SearchGifsQuery;
use Prex\Gifs\Domain\Exception\GifCatalogUnavailable;
use Prex\Gifs\Domain\Exception\InvalidPagination;
use Prex\Gifs\Domain\Exception\InvalidSearchTerm;
use Prex\Gifs\Domain\Pagination;
use Tests\Doubles\FakeGifCatalog;

final class SearchGifsHandlerTest extends TestCase
{
    private FakeGifCatalog $catalog;

    private SearchGifsHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catalog = FakeGifCatalog::withGifs(
            FakeGifCatalog::gif('gif-1', 'A funny cat'),
            FakeGifCatalog::gif('gif-2', 'Another funny cat'),
            FakeGifCatalog::gif('gif-3', 'A sleepy dog'),
        );

        $this->handler = new SearchGifsHandler($this->catalog);
    }

    #[Test]
    public function it_returns_the_gifs_matching_the_term(): void
    {
        $result = $this->handler->handle(new SearchGifsQuery('cat'));

        self::assertCount(2, $result);
        self::assertSame(2, $result->totalCount);
        self::assertSame(['gif-1', 'gif-2'], array_map(fn ($gif) => $gif->id->value, $result->gifs));
    }

    #[Test]
    public function it_applies_the_default_pagination_when_none_is_given(): void
    {
        $this->handler->handle(new SearchGifsQuery('cat'));

        self::assertSame(Pagination::DEFAULT_LIMIT, $this->catalog->lastCriteria?->pagination->limit);
        self::assertSame(0, $this->catalog->lastCriteria?->pagination->offset);
    }

    #[Test]
    public function it_forwards_limit_and_offset_to_the_catalog(): void
    {
        $result = $this->handler->handle(new SearchGifsQuery('cat', limit: 1, offset: 1));

        self::assertCount(1, $result);
        self::assertSame('gif-2', $result->gifs[0]->id->value);
        self::assertSame(2, $result->totalCount, 'The total is the whole result set, not the page.');
    }

    #[Test]
    public function it_returns_an_empty_result_when_nothing_matches(): void
    {
        $result = $this->handler->handle(new SearchGifsQuery('unicorn'));

        self::assertTrue($result->isEmpty());
        self::assertSame(0, $result->totalCount);
    }

    #[Test]
    public function it_rejects_an_empty_term_before_calling_the_catalog(): void
    {
        $this->expectException(InvalidSearchTerm::class);

        $this->handler->handle(new SearchGifsQuery('  '));
    }

    #[Test]
    public function it_rejects_an_out_of_range_limit(): void
    {
        $this->expectException(InvalidPagination::class);

        $this->handler->handle(new SearchGifsQuery('cat', limit: 500));
    }

    #[Test]
    public function it_surfaces_a_catalog_outage_as_a_domain_error(): void
    {
        $this->catalog->makeUnavailable();

        $this->expectException(GifCatalogUnavailable::class);

        $this->handler->handle(new SearchGifsQuery('cat'));
    }
}
