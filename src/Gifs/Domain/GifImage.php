<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

final readonly class GifImage
{
    public function __construct(
        public string $url,
        public ?int $width = null,
        public ?int $height = null,
        public ?int $sizeInBytes = null,
    ) {}
}
