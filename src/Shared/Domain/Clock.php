<?php

declare(strict_types=1);

namespace Prex\Shared\Domain;

use DateTimeImmutable;

/**
 * Port that gives the domain access to the current time without binding it to
 * a concrete implementation, which keeps time dependent rules testable.
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}
