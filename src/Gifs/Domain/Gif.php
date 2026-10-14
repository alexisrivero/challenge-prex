<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

/**
 * A GIF as modelled by this application.
 *
 * It is an immutable snapshot of a resource owned by an external catalog, so it
 * exposes only the information our clients need, in our own language, and never
 * the raw provider payload.
 */
final readonly class Gif
{
    public function __construct(
        public GifId $id,
        public string $title,
        public string $url,
        public ?string $rating = null,
        public ?string $username = null,
        public ?GifImage $original = null,
        public ?GifImage $preview = null,
    ) {}
}
