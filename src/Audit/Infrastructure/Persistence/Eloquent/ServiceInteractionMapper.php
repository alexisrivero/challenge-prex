<?php

declare(strict_types=1);

namespace Prex\Audit\Infrastructure\Persistence\Eloquent;

use DateTimeImmutable;
use DateTimeZone;
use Prex\Audit\Domain\ConsumedService;
use Prex\Audit\Domain\HttpStatusCode;
use Prex\Audit\Domain\IpAddress;
use Prex\Audit\Domain\Payload;
use Prex\Audit\Domain\ServiceInteraction;
use Prex\Audit\Domain\ServiceInteractionId;
use Prex\Shared\Domain\ValueObject\UserId;

final readonly class ServiceInteractionMapper
{
    public function toDomain(ServiceInteractionModel $model): ServiceInteraction
    {
        return new ServiceInteraction(
            id: ServiceInteractionId::fromString((string) $model->id),
            userId: $model->user_id === null ? null : UserId::fromInt((int) $model->user_id),
            service: ConsumedService::create(
                (string) $model->http_method,
                (string) $model->path,
                $model->service_name
            ),
            requestBody: Payload::fromArray($model->request_body ?? []),
            responseStatus: HttpStatusCode::fromInt((int) $model->response_status),
            responseBody: Payload::fromArray($model->response_body ?? []),
            origin: IpAddress::fromString((string) $model->ip_address),
            occurredAt: new DateTimeImmutable(
                $model->occurred_at->toDateTimeString(),
                new DateTimeZone('UTC')
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(ServiceInteraction $interaction): array
    {
        return [
            'id' => $interaction->id->value,
            'user_id' => $interaction->userId?->value,
            'http_method' => $interaction->service->method,
            'path' => $interaction->service->path,
            'service_name' => $interaction->service->name,
            'request_body' => $interaction->requestBody->toArray(),
            'response_status' => $interaction->responseStatus->value,
            'response_body' => $interaction->responseBody->toArray(),
            'ip_address' => $interaction->origin->value,
            'occurred_at' => $interaction->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
