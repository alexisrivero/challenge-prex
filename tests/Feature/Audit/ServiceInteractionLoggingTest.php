<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Prex\Audit\Infrastructure\Persistence\Eloquent\ServiceInteractionModel;
use Prex\Auth\Infrastructure\Persistence\Eloquent\UserModel;
use Tests\Concerns\FakesGiphy;
use Tests\Concerns\InteractsWithOAuth;
use Tests\TestCase;

/**
 * Requirement 3 of the challenge: every interaction with the services must be
 * persisted with the user, the service, the request body, the response status,
 * the response body and the origin IP.
 */
final class ServiceInteractionLoggingTest extends TestCase
{
    use FakesGiphy, InteractsWithOAuth, RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        Passport::actingAs($this->user);
    }

    #[Test]
    public function a_successful_search_is_fully_audited(): void
    {
        $this->fakeGiphySearch(['gif-1']);

        $this->getJson('/api/v1/gifs?query=cats&limit=1', ['REMOTE_ADDR' => '203.0.113.7'])->assertOk();

        $interaction = ServiceInteractionModel::query()->sole();

        self::assertSame($this->user->id, $interaction->user_id);
        self::assertSame('gifs.search', $interaction->service_name);
        self::assertSame('GET', $interaction->http_method);
        self::assertSame('/api/v1/gifs', $interaction->path);
        self::assertSame(['query' => 'cats', 'limit' => '1'], $interaction->request_body);
        self::assertSame(200, $interaction->response_status);
        self::assertSame('gif-1', $interaction->response_body['data'][0]['id']);
        self::assertNotEmpty($interaction->ip_address);
        self::assertNotNull($interaction->occurred_at);
    }

    #[Test]
    public function it_records_the_origin_ip_of_the_caller(): void
    {
        $this->fakeGiphySearch(['gif-1']);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.23'])
            ->getJson('/api/v1/gifs?query=cats')
            ->assertOk();

        self::assertSame('198.51.100.23', ServiceInteractionModel::query()->sole()->ip_address);
    }

    #[Test]
    public function a_failed_interaction_is_audited_with_its_error_response(): void
    {
        $this->fakeGiphyOutage();

        $this->getJson('/api/v1/gifs?query=cats')->assertStatus(503);

        $interaction = ServiceInteractionModel::query()->sole();

        self::assertSame(503, $interaction->response_status);
        self::assertSame('gif_catalog_unavailable', $interaction->response_body['error']['code']);
    }

    #[Test]
    public function a_rejected_request_is_audited_too(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/gifs?query=cats')->assertUnauthorized();

        $interaction = ServiceInteractionModel::query()->sole();

        self::assertNull($interaction->user_id);
        self::assertSame(401, $interaction->response_status);
        self::assertSame('unauthenticated', $interaction->response_body['error']['code']);
    }

    #[Test]
    public function every_service_call_produces_exactly_one_record(): void
    {
        $this->fakeGiphySearch(['gif-1']);

        $this->getJson('/api/v1/gifs?query=cats')->assertOk();
        $this->getJson('/api/v1/gifs?query=dogs')->assertOk();

        self::assertSame(2, ServiceInteractionModel::query()->count());
    }

    #[Test]
    public function auditing_can_be_switched_off_by_configuration(): void
    {
        config()->set('audit.enabled', false);
        $this->fakeGiphySearch(['gif-1']);

        $this->getJson('/api/v1/gifs?query=cats')->assertOk();

        $this->assertDatabaseEmpty('service_interactions');
    }
}
