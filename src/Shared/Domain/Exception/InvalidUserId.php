<?php

declare(strict_types=1);

namespace Prex\Shared\Domain\Exception;

final class InvalidUserId extends DomainException
{
    public static function notPositive(int $value): self
    {
        return new self(sprintf('The user id must be a positive integer, "%d" given.', $value));
    }

    public static function notNumeric(string $value): self
    {
        return new self(sprintf('The user id must be numeric, "%s" given.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid_user_id';
    }
}
