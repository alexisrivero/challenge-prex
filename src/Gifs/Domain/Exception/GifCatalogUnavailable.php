<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;
use Prex\Shared\Domain\Exception\ServiceUnavailable;
use Throwable;

final class GifCatalogUnavailable extends DomainException implements ServiceUnavailable
{
    public static function because(string $reason, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('The gif catalog is temporarily unavailable: %s', $reason),
            0,
            $previous
        );
    }

    public function errorCode(): string
    {
        return 'gif_catalog_unavailable';
    }
}
