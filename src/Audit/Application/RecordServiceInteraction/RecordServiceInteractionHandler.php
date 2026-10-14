<?php

declare(strict_types=1);

namespace Prex\Audit\Application\RecordServiceInteraction;

use Prex\Audit\Domain\ConsumedService;
use Prex\Audit\Domain\HttpStatusCode;
use Prex\Audit\Domain\IpAddress;
use Prex\Audit\Domain\Payload;
use Prex\Audit\Domain\ServiceInteraction;
use Prex\Audit\Domain\ServiceInteractionRepository;
use Prex\Shared\Domain\Clock;
use Prex\Shared\Domain\ValueObject\UserId;

/**
 * Use case: persist every interaction with the API.
 *
 * It is invoked by the auditing middleware, but nothing stops a queue worker or
 * a CLI importer from reusing it: that is the point of keeping it transport
 * agnostic.
 */
final readonly class RecordServiceInteractionHandler
{
    /**
     * @param  list<string>  $redactedKeys  payload keys that must never be stored in clear text
     */
    public function __construct(
        private ServiceInteractionRepository $repository,
        private Clock $clock,
        private array $redactedKeys = [],
        private int $maxPayloadBytes = 65535,
    ) {}

    public function handle(RecordServiceInteractionCommand $command): ServiceInteraction
    {
        $interaction = ServiceInteraction::record(
            userId: $command->userId === null ? null : UserId::fromInt($command->userId),
            service: ConsumedService::create($command->httpMethod, $command->path, $command->serviceName),
            requestBody: $this->sanitise(Payload::fromMixed($command->requestBody)),
            responseStatus: HttpStatusCode::fromInt($command->statusCode),
            responseBody: $this->sanitise(Payload::fromMixed($command->responseBody)),
            origin: IpAddress::fromString($command->ipAddress),
            occurredAt: $this->clock->now(),
        );

        $this->repository->save($interaction);

        return $interaction;
    }

    private function sanitise(Payload $payload): Payload
    {
        return $payload
            ->redacting($this->redactedKeys)
            ->truncated($this->maxPayloadBytes);
    }
}
