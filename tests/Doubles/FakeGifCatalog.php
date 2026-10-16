<?php

declare(strict_types=1);

namespace Tests\Doubles;

use Prex\Gifs\Domain\Exception\GifCatalogUnavailable;
use Prex\Gifs\Domain\Exception\GifNotFound;
use Prex\Gifs\Domain\Gif;
use Prex\Gifs\Domain\GifCatalog;
use Prex\Gifs\Domain\GifId;
use Prex\Gifs\Domain\GifImage;
use Prex\Gifs\Domain\GifSearchResult;
use Prex\Gifs\Domain\SearchCriteria;

/**
 * In-memory catalog: lets the use cases be tested without GIPHY nor HTTP.
 */
final class FakeGifCatalog implements GifCatalog
{
    /** @var array<string, Gif> */
    private array $gifs = [];

    private bool $unavailable = false;

    public ?SearchCriteria $lastCriteria = null;

    public int $findByIdCalls = 0;

    public static function withGifs(Gif ...$gifs): self
    {
        $catalog = new self;

        foreach ($gifs as $gif) {
            $catalog->gifs[$gif->id->value] = $gif;
        }

        return $catalog;
    }

    public static function gif(string $id, string $title = 'A funny gif'): Gif
    {
        return new Gif(
            id: GifId::fromString($id),
            title: $title,
            url: 'https://giphy.com/gifs/'.$id,
            rating: 'g',
            username: 'prex',
            original: new GifImage('https://media.giphy.com/media/'.$id.'/giphy.gif', 480, 270, 1024),
            preview: new GifImage('https://media.giphy.com/media/'.$id.'/preview.gif', 200, 112, 256),
        );
    }

    public function makeUnavailable(): void
    {
        $this->unavailable = true;
    }

    public function search(SearchCriteria $criteria): GifSearchResult
    {
        $this->guardAvailability();

        $this->lastCriteria = $criteria;

        $matching = array_values(array_filter(
            $this->gifs,
            fn (Gif $gif): bool => str_contains(
                strtolower($gif->title),
                strtolower($criteria->term->value)
            )
        ));

        $page = array_slice($matching, $criteria->pagination->offset, $criteria->pagination->limit);

        return new GifSearchResult(
            gifs: array_values($page),
            pagination: $criteria->pagination,
            totalCount: count($matching),
        );
    }

    public function findById(GifId $id): Gif
    {
        $this->findByIdCalls++;

        $this->guardAvailability();

        return $this->gifs[$id->value] ?? throw GifNotFound::withId($id);
    }

    private function guardAvailability(): void
    {
        if ($this->unavailable) {
            throw GifCatalogUnavailable::because('the fake catalog was made unavailable');
        }
    }
}
