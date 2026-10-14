<?php

declare(strict_types=1);

namespace Prex\Audit\Application\RecordServiceInteraction;

/**
 * Input of the audit use case, expressed with primitives: the middleware that
 * builds it does not need to know the domain model.
 */
final readonly class RecordServiceInteractionCommand
{
    /**
     * @param  array<array-key, mixed>  $requestBody
     */
    public function __construct(
        public ?int $userId,
        public string $httpMethod,
        public string $path,
        public ?string $serviceName,
        public array $requestBody,
        public int $statusCode,
        public mixed $responseBody,
        public ?string $ipAddress,
    ) {}
}
