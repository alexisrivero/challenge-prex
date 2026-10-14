<?php

declare(strict_types=1);

namespace Prex\Audit\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;

final class InvalidHttpStatusCode extends DomainException
{
    public static function withValue(int $value): self
    {
        return new self(sprintf('"%d" is not a valid HTTP status code.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid_http_status_code';
    }
}
