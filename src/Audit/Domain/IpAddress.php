<?php

declare(strict_types=1);

namespace Prex\Audit\Domain;

use Prex\Audit\Domain\Exception\InvalidIpAddress;

final readonly class IpAddress
{
    public const string UNKNOWN = 'unknown';

    private function __construct(public string $value) {}

    public static function fromString(?string $value): self
    {
        $value = trim((string) $value);

        if ($value === '') {
            return new self(self::UNKNOWN);
        }

        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            throw InvalidIpAddress::withValue($value);
        }

        return new self($value);
    }

    public function isKnown(): bool
    {
        return $this->value !== self::UNKNOWN;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
