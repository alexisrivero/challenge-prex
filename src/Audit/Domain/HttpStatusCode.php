<?php

declare(strict_types=1);

namespace Prex\Audit\Domain;

use Prex\Audit\Domain\Exception\InvalidHttpStatusCode;

final readonly class HttpStatusCode
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 100 || $value > 599) {
            throw InvalidHttpStatusCode::withValue($value);
        }

        return new self($value);
    }

    public function isSuccessful(): bool
    {
        return $this->value >= 200 && $this->value < 300;
    }

    public function isError(): bool
    {
        return $this->value >= 400;
    }
}
