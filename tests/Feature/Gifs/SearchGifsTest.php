<?php

declare(strict_types=1);

namespace Tests\Feature\Gifs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Prex\Auth\Infrastructure\Persistence\Eloquent\UserModel;
use Tests\Concerns\FakesGiphy;
use Tests\Concerns\InteractsWithOAuth;
use Tests\TestCase;

final class SearchGifsTest extends TestCase
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
    public function it_returns_the_gifs_reported_by_the_catalog(): void
    {
        $this->fakeGiphySearch(['gif-1', 'gif-2'], totalCount: 137);

        $this->getJson('/api/v1/gifs?query=funny+cat&limit=2&offset=0')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', 'gif-1')
            ->assertJsonPath('data.0.title', 'Funny cat gif-1')
            ->assertJsonPath('data.0.url', 'https://giphy.com/gifs/gif-1')
            ->assertJsonPath('data.0.images.original.url', 'https://media.giphy.com/media/gif-1/giphy.gif')
            ->assertJsonPath('data.0.images.original.width', 480)
            ->assertJsonPath('meta.count', 2)
            ->assertJsonPath('meta.total_count', 137)
            ->assertJsonPath('meta.limit', 2)
            ->assertJsonPath('meta.offset', 0);
    }

    #[Test]
    public function it_forwards_the_filters_to_the_provider(): void
    {
        $this->fakeGiphySearch(['gif-1']);

        $this->getJson('/api/v1/gifs?query=funny+cat&limit=5&offset=10')->assertOk();

        Http::assertSent(function ($request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_contains($request->url(), '/gifs/search')
                && $query['q'] === 'funny cat'
                && $query['limit'] === '5'
                && $query['offset'] === '10'
                && $query['api_key'] === 'testing-giphy-key';
        });
    }

    #[Test]
    public function limit_and_offset_are_optional(): void
    {
        $this->fakeGiphySearch(['gif-1']);

        $this->getJson('/api/v1/gifs?query=cats')
            ->assertOk()
            ->assertJsonPath('meta.limit', 25)
            ->assertJsonPath('meta.offset', 0);
    }

    #[Test]
    public function it_also_accepts_the_upper_case_filters_of_the_specification(): void
    {
        $this->fakeGiphySearch(['gif-1']);

        $this->getJson('/api/v1/gifs?QUERY=cats&LIMIT=1&OFFSET=0')
            ->assertOk()
            ->assertJsonPath('meta.limit', 1);
    }

    #[Test]
    public function it_returns_an_empty_collection_when_there_are_no_matches(): void
    {
        $this->fakeGiphySearch([]);

        $this->getJson('/api/v1/gifs?query=unicorn')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total_count', 0);
    }

    #[Test]
    public function the_query_filter_is_required(): void
    {
        $this->getJson('/api/v1/gifs')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['errors' => ['query']]]);
    }

    #[Test]
    public function it_rejects_a_limit_above_the_maximum(): void
    {
        $this->getJson('/api/v1/gifs?query=cats&limit=500')
            ->assertUnprocessable()
            ->assertJsonStructure(['error' => ['errors' => ['limit']]]);
    }

    #[Test]
    public function it_reports_a_provider_outage_as_service_unavailable(): void
    {
        $this->fakeGiphyOutage();

        $this->getJson('/api/v1/gifs?query=cats')
            ->assertStatus(503)
            ->assertJsonPath('error.code', 'gif_catalog_unavailable');
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        // Drop the token issued by Passport::actingAs().
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/gifs?query=cats')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }
}
