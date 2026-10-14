<?php

declare(strict_types=1);

namespace Prex\Audit\Domain;

use Prex\Audit\Domain\Exception\InvalidServiceInteractionId;
use Prex\Shared\Domain\ValueObject\Uuid;

final readonly class ServiceInteractionId
{
    private function __construct(public string $value) {}

    public static function generate(): self
    {
        return new self(Uuid::v4());
    }

    public static function fromString(string $value): self
    {
        if (! Uuid::isValid($value)) {
            throw InvalidServiceInteractionId::withValue($value);
        }

        return new self(strtolower($value));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
