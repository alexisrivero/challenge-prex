<?php

declare(strict_types=1);

namespace Tests\Feature\Gifs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\Concerns\FakesGiphy;
use Tests\Concerns\InteractsWithOAuth;
use Tests\TestCase;

final class FindGifTest extends TestCase
{
    use FakesGiphy, InteractsWithOAuth, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Passport::actingAs($this->createUser());
    }

    #[Test]
    public function it_returns_the_requested_gif(): void
    {
        $this->fakeGiphyGif('3oEjI6SIIHBdRxXI40');

        $this->getJson('/api/v1/gifs/3oEjI6SIIHBdRxXI40')
            ->assertOk()
            ->assertJsonPath('data.id', '3oEjI6SIIHBdRxXI40')
            ->assertJsonPath('data.title', 'Funny cat 3oEjI6SIIHBdRxXI40')
            ->assertJsonPath('data.rating', 'g')
            ->assertJsonPath('data.images.preview.width', 200);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/gifs/3oEjI6SIIHBdRxXI40'));
    }

    #[Test]
    public function it_returns_not_found_when_the_provider_does_not_know_the_gif(): void
    {
        $this->fakeGiphyNotFound();

        $this->getJson('/api/v1/gifs/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'gif_not_found')
            ->assertJsonPath('error.context.gif_id', 'does-not-exist');
    }

    #[Test]
    public function it_returns_not_found_when_the_provider_answers_with_an_empty_payload(): void
    {
        Http::fake(['*' => Http::response(['data' => [], 'meta' => ['status' => 200]], 200)]);

        $this->getJson('/api/v1/gifs/empty-payload')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'gif_not_found');
    }

    #[Test]
    public function an_id_with_unexpected_characters_does_not_even_match_the_route(): void
    {
        $this->getJson('/api/v1/gifs/not$valid')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'endpoint_not_found');
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/gifs/3oEjI6SIIHBdRxXI40')->assertUnauthorized();
    }

    /**
     * Regression: GIPHY answers 400 for an id it considers malformed, and we
     * turned that into a 503 "catalog unavailable" for an id the client could
     * do nothing about.
     */
    #[Test]
    public function an_id_the_provider_rejects_is_reported_as_not_found(): void
    {
        Http::fake(['*' => Http::response(['meta' => ['status' => 400]], 400)]);

        $this->getJson('/api/v1/gifs/a-b_c')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'gif_not_found');
    }

    /**
     * Regression: the catch-all branch copied the transport error message into
     * the response and into the audit trail, and a transport message carries
     * the full request URL, `api_key` included.
     */
    #[Test]
    public function a_transport_failure_never_leaks_the_api_key(): void
    {
        $key = 'super-secret-giphy-key';

        Http::fake(function () use ($key): never {
            throw new RuntimeException(
                'cURL error 35: SSL connect error for https://api.giphy.com/v1/gifs/gif-1?api_key='.$key
            );
        });

        $response = $this->getJson('/api/v1/gifs/gif-1')->assertStatus(503);

        self::assertStringNotContainsString($key, $response->getContent() ?: '');
        self::assertStringNotContainsString('api_key', $response->getContent() ?: '');
    }
}
