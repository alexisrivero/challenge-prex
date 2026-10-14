<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

use SensitiveParameter;

final readonly class HashedPassword
{
    private function __construct(private string $value) {}

    public static function fromHash(#[SensitiveParameter] string $hash): self
    {
        return new self($hash);
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['value' => '***redacted***'];
    }
}
