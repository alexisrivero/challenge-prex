<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Exception;

use Prex\Gifs\Domain\GifId;
use Prex\Shared\Domain\Exception\DomainException;
use Prex\Shared\Domain\Exception\NotFound;

final class GifNotFound extends DomainException implements NotFound
{
    private function __construct(string $message, private readonly string $gifId)
    {
        parent::__construct($message);
    }

    public static function withId(GifId $id): self
    {
        return new self(sprintf('No gif found with id "%s".', $id->value), $id->value);
    }

    public function errorCode(): string
    {
        return 'gif_not_found';
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return ['gif_id' => $this->gifId];
    }
}
