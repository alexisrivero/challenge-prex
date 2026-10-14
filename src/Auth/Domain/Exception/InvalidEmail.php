<?php

declare(strict_types=1);

namespace Prex\Auth\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;

final class InvalidEmail extends DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('"%s" is not a valid email address.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid_email';
    }
}
