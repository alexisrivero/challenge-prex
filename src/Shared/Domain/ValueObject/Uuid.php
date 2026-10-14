<?php

declare(strict_types=1);

namespace Prex\Shared\Domain\ValueObject;

use Random\RandomException;

/**
 * Minimal, dependency free UUID v4 generator/validator used by the identities
 * of the aggregates. Keeping it in the shared kernel avoids repeating the
 * algorithm in every bounded context and keeps the domain free of vendor code.
 */
final readonly class Uuid
{
    public const string PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * @throws RandomException
     */
    public static function v4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    public static function isValid(string $value): bool
    {
        return preg_match(self::PATTERN, $value) === 1;
    }
}
