<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Exception;

use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\Exception\DomainException;

final class InvalidGifId extends DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf(
            'The gif id must be an alphanumeric value of up to %d characters, "%s" given.',
            GifId::MAX_LENGTH,
            $value
        ));
    }

    public function errorCode(): string
    {
        return 'invalid_gif_id';
    }
}
