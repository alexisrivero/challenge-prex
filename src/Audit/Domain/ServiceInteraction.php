<?php

declare(strict_types=1);

namespace Prex\Audit\Domain;

use DateTimeImmutable;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Aggregate root of the Audit context: one call to one of our services.
 *
 * It records exactly what the challenge asks for: who called, which service,
 * the request body, the HTTP status, the response body and the origin IP.
 */
final readonly class ServiceInteraction
{
    public function __construct(
        public ServiceInteractionId $id,
        public ?UserId $userId,
        public ConsumedService $service,
        public Payload $requestBody,
        public HttpStatusCode $responseStatus,
        public Payload $responseBody,
        public IpAddress $origin,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function record(
        ?UserId $userId,
        ConsumedService $service,
        Payload $requestBody,
        HttpStatusCode $responseStatus,
        Payload $responseBody,
        IpAddress $origin,
        DateTimeImmutable $occurredAt,
    ): self {
        return new self(
            ServiceInteractionId::generate(),
            $userId,
            $service,
            $requestBody,
            $responseStatus,
            $responseBody,
            $origin,
            $occurredAt,
        );
    }

    public function wasAnonymous(): bool
    {
        return $this->userId === null;
    }

    public function failed(): bool
    {
        return $this->responseStatus->isError();
    }
}
