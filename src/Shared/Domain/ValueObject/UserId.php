<?php

declare(strict_types=1);

namespace Prex\Shared\Domain\ValueObject;

use Prex\Shared\Domain\Exception\InvalidUserId;

/**
 * Identity of a user.
 *
 * It lives in the shared kernel because more than one bounded context needs to
 * reference a user (the Auth context owns the user, while Gifs and Audit only
 * point at it).
 */
final readonly class UserId
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value <= 0) {
            throw InvalidUserId::notPositive($value);
        }

        return new self($value);
    }

    public static function fromString(string $value): self
    {
        if (! ctype_digit($value)) {
            throw InvalidUserId::notNumeric($value);
        }

        return self::fromInt((int) $value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
