<?php

declare(strict_types=1);

namespace Prex\Gifs\Domain\Favorite;

use Prex\Gifs\Domain\Favorite\Exception\InvalidAlias;

/**
 * The name a user gives to one of their favorite GIFs.
 */
final readonly class Alias
{
    public const int MIN_LENGTH = 1;

    public const int MAX_LENGTH = 100;

    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $normalised = trim(preg_replace('/\s+/', ' ', $value) ?? '');
        $length = mb_strlen($normalised);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw InvalidAlias::withValue($value);
        }

        return new self($normalised);
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
