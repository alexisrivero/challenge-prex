<?php

declare(strict_types=1);

namespace Prex\Audit\Domain\Exception;

use Prex\Shared\Domain\Exception\DomainException;

final class InvalidConsumedService extends DomainException
{
    public static function missingMethod(): self
    {
        return new self('The consumed service requires an HTTP method.');
    }

    public function errorCode(): string
    {
        return 'invalid_consumed_service';
    }
}
