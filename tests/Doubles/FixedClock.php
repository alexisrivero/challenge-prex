<?php

declare(strict_types=1);

namespace Tests\Doubles;

use DateTimeImmutable;
use DateTimeZone;
use Prex\Shared\Domain\Clock;

/**
 * Deterministic clock: time dependent assertions become trivial.
 */
final class FixedClock implements Clock
{
    private DateTimeImmutable $now;

    public function __construct(string $now = '2026-03-05 13:37:16')
    {
        $this->now = new DateTimeImmutable($now, new DateTimeZone('UTC'));
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function travelTo(string $moment): void
    {
        $this->now = new DateTimeImmutable($moment, new DateTimeZone('UTC'));
    }
}
