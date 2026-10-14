<?php

declare(strict_types=1);

namespace Prex\Gifs\Application\FindGif;

final readonly class FindGifQuery
{
    public function __construct(public string $gifId) {}
}
