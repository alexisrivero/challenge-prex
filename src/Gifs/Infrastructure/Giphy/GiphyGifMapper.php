<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Giphy;

use Prex\Gifs\Domain\Gif;
use Prex\Gifs\Domain\GifId;
use Prex\Gifs\Domain\GifImage;

/**
 * Translates a raw GIPHY payload into the domain model.
 *
 * All the knowledge about the provider's JSON shape is confined here, so a
 * change on their side never leaks into the domain or the use cases.
 */
final readonly class GiphyGifMapper
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function toDomain(array $payload): Gif
    {
        /** @var array<string, mixed> $images */
        $images = is_array($payload['images'] ?? null) ? $payload['images'] : [];

        $title = trim((string) ($payload['title'] ?? ''));

        return new Gif(
            id: GifId::fromString((string) ($payload['id'] ?? '')),
            title: $title !== '' ? $title : 'Untitled',
            url: (string) ($payload['url'] ?? ''),
            rating: $this->nullableString($payload['rating'] ?? null),
            username: $this->nullableString($payload['username'] ?? null),
            original: $this->image($images['original'] ?? null),
            preview: $this->image($images['fixed_width_downsampled'] ?? $images['preview_gif'] ?? null),
        );
    }

    private function image(mixed $raw): ?GifImage
    {
        if (! is_array($raw) || ! isset($raw['url'])) {
            return null;
        }

        return new GifImage(
            url: (string) $raw['url'],
            width: $this->nullableInt($raw['width'] ?? null),
            height: $this->nullableInt($raw['height'] ?? null),
            sizeInBytes: $this->nullableInt($raw['size'] ?? null),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
