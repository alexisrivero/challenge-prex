<?php

declare(strict_types=1);

namespace Prex\Auth\Domain;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * An OAuth2 bearer access token already issued for a user.
 */
final readonly class AccessToken
{
    public const string TYPE_BEARER = 'Bearer';

    public function __construct(
        #[SensitiveParameter] private string $value,
        public DateTimeImmutable $expiresAt,
        public string $type = self::TYPE_BEARER,
    ) {}

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Remaining lifetime, in seconds, measured from the given instant.
     */
    public function expiresIn(DateTimeImmutable $now): int
    {
        return max(0, $this->expiresAt->getTimestamp() - $now->getTimestamp());
    }

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['value' => '***redacted***', 'expiresAt' => $this->expiresAt->format(DATE_ATOM)];
    }
}
