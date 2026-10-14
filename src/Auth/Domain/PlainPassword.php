<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

use Prex\Auth\Domain\Exception\InvalidPassword;
use SensitiveParameter;

/**
 * A password as typed by the user. It is never persisted nor logged: the only
 * thing the domain does with it is handing it over to the PasswordHasher port.
 */
final readonly class PlainPassword
{
    public const int MIN_LENGTH = 8;

    private function __construct(private string $value) {}

    public static function fromString(#[SensitiveParameter] string $value): self
    {
        if ($value === '') {
            throw InvalidPassword::empty();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Hide the secret from stack traces, logs and dumps.
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['value' => '***redacted***'];
    }
}
