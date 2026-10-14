<?php

declare(strict_types=1);

namespace Prex\Shared\Infrastructure\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Prex\Shared\Domain\Clock;

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
