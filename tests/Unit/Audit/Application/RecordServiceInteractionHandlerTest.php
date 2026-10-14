<?php

declare(strict_types=1);

namespace Tests\Unit\Audit\Application;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prex\Audit\Application\RecordServiceInteraction\RecordServiceInteractionCommand;
use Prex\Audit\Application\RecordServiceInteraction\RecordServiceInteractionHandler;
use Prex\Audit\Domain\Payload;
use Tests\Doubles\FixedClock;
use Tests\Doubles\InMemoryServiceInteractionRepository;

final class RecordServiceInteractionHandlerTest extends TestCase
{
    private InMemoryServiceInteractionRepository $repository;

    private RecordServiceInteractionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new InMemoryServiceInteractionRepository;
        $this->handler = new RecordServiceInteractionHandler(
            $this->repository,
            new FixedClock('2026-03-05 13:37:16'),
            redactedKeys: ['password', 'access_token'],
            maxPayloadBytes: 200,
        );
    }

    #[Test]
    public function it_records_everything_the_challenge_asks_for(): void
    {
        $interaction = $this->handler->handle(new RecordServiceInteractionCommand(
            userId: 7,
            httpMethod: 'post',
            path: 'api/v1/gifs/favorites',
            serviceName: 'gifs.favorites.store',
            requestBody: ['gif_id' => 'gif-1', 'alias' => 'my cat', 'user_id' => 7],
            statusCode: 201,
            responseBody: '{"data":{"id":"uuid"}}',
            ipAddress: '10.0.0.7',
        ));

        self::assertCount(1, $this->repository->saved);
        self::assertSame(7, $interaction->userId?->value);
        self::assertSame('POST', $interaction->service->method);
        self::assertSame('/api/v1/gifs/favorites', $interaction->service->path);
        self::assertSame('gifs.favorites.store', $interaction->service->name);
        self::assertSame('POST /api/v1/gifs/favorites', $interaction->service->signature());
        self::assertSame(201, $interaction->responseStatus->value);
        self::assertSame(['data' => ['id' => 'uuid']], $interaction->responseBody->toArray());
        self::assertSame('10.0.0.7', $interaction->origin->value);
        self::assertSame('2026-03-05 13:37:16', $interaction->occurredAt->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_never_stores_credentials_in_clear_text(): void
    {
        $interaction = $this->handler->handle(new RecordServiceInteractionCommand(
            userId: null,
            httpMethod: 'POST',
            path: '/api/v1/auth/login',
            serviceName: 'auth.login',
            requestBody: ['email' => 'ada@prex.local', 'password' => 'super-secret'],
            statusCode: 200,
            responseBody: '{"data":{"access_token":"jwt","token_type":"Bearer"}}',
            ipAddress: '10.0.0.7',
        ));

        self::assertSame(Payload::REDACTED, $interaction->requestBody->toArray()['password']);
        self::assertSame('ada@prex.local', $interaction->requestBody->toArray()['email']);
        self::assertSame(Payload::REDACTED, $interaction->responseBody->toArray()['data']['access_token']);
    }

    #[Test]
    public function an_anonymous_interaction_is_recorded_without_a_user(): void
    {
        $interaction = $this->handler->handle(new RecordServiceInteractionCommand(
            userId: null,
            httpMethod: 'GET',
            path: '/api/v1/gifs',
            serviceName: 'gifs.search',
            requestBody: ['query' => 'cats'],
            statusCode: 401,
            responseBody: '{"error":{"code":"unauthenticated"}}',
            ipAddress: null,
        ));

        self::assertTrue($interaction->wasAnonymous());
        self::assertTrue($interaction->failed());
        self::assertFalse($interaction->origin->isKnown());
    }

    #[Test]
    public function it_truncates_oversized_bodies(): void
    {
        $interaction = $this->handler->handle(new RecordServiceInteractionCommand(
            userId: 7,
            httpMethod: 'GET',
            path: '/api/v1/gifs',
            serviceName: 'gifs.search',
            requestBody: ['query' => 'cats'],
            statusCode: 200,
            responseBody: json_encode(['data' => str_repeat('x', 5000)]),
            ipAddress: '10.0.0.7',
        ));

        self::assertTrue($interaction->responseBody->toArray()[Payload::TRUNCATED_KEY] ?? false);
    }
}
