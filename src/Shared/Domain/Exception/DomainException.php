<?php

declare(strict_types=1);

namespace Prex\Shared\Domain\Exception;

use RuntimeException;

/**
 * Base class for every error that belongs to the domain language.
 *
 * The domain never knows about HTTP: it only states *what* went wrong through
 * an error code and, optionally, one of the marker interfaces of this
 * namespace. Translating that into a transport specific status code is the
 * responsibility of the infrastructure layer.
 */
abstract class DomainException extends RuntimeException
{
    /**
     * Stable, machine readable identifier of the error (snake_case).
     */
    abstract public function errorCode(): string;

    /**
     * Extra, serialisable context to help the client understand the failure.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [];
    }
}
