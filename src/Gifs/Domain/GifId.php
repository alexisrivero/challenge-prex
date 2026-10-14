<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain;

use Prex\Gifs\Domain\Exception\InvalidGifId;

/**
 * Identity of a GIF inside the external catalog.
 *
 * NOTE: the challenge describes GIF_ID / ID as "numérico", but GIPHY identifies
 * its resources with alphanumeric ids (e.g. "3oEjI6SIIHBdRxXI40"). Restricting
 * the value object to digits would make the API unusable against the real
 * provider, so the invariant implemented here is "a non empty, URL safe
 * alphanumeric identifier" - which also accepts purely numeric ids.
 */
final readonly class GifId
{
    public const int MAX_LENGTH = 64;

    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if ($trimmed === '' || strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidGifId::withValue($value);
        }

        if (preg_match('/^[A-Za-z0-9_-]+$/', $trimmed) !== 1) {
            throw InvalidGifId::withValue($value);
        }

        return new self($trimmed);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
