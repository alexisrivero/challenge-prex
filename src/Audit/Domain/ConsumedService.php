<?php

declare(strict_types=1);

namespace Prex\Audit\Domain;

use Prex\Audit\Domain\Exception\InvalidConsumedService;

/**
 * Identifies the service that was consulted: the HTTP verb, the path and -
 * when the route is named - the logical name of the use case ("gifs.search").
 */
final readonly class ConsumedService
{
    private function __construct(
        public string $method,
        public string $path,
        public ?string $name,
    ) {}

    public static function create(string $method, string $path, ?string $name = null): self
    {
        $method = strtoupper(trim($method));
        $path = '/'.ltrim(trim($path), '/');

        if ($method === '') {
            throw InvalidConsumedService::missingMethod();
        }

        return new self($method, $path, $name === null || trim($name) === '' ? null : trim($name));
    }

    /**
     * Human readable identifier, e.g. "POST /api/v1/gifs/favorites".
     */
    public function signature(): string
    {
        return $this->method.' '.$this->path;
    }

    public function __toString(): string
    {
        return $this->name ?? $this->signature();
    }
}
