<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

use Prex\Auth\Domain\Exception\InvalidEmail;

final readonly class Email
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $normalised = strtolower(trim($value));

        if ($normalised === '' || filter_var($normalised, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidEmail::withValue($value);
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
