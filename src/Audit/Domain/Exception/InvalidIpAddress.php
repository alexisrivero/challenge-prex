<?php

declare(strict_types=1);

namespace Prex\Audit\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;

final class InvalidIpAddress extends DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('"%s" is not a valid IP address.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid_ip_address';
    }
}
