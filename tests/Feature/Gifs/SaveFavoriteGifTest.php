<?php

declare(strict_types=1);

namespace Tests\Feature\Gifs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Prex\Auth\Infrastructure\Persistence\Eloquent\UserModel;
use Prex\Gifs\Domain\Favorite\Alias;
use Prex\Gifs\Domain\Favorite\Exception\FavoriteGifAlreadySaved;
use Prex\Gifs\Domain\Favorite\FavoriteGif;
use Prex\Gifs\Domain\Favorite\FavoriteGifRepository;
use Prex\Gifs\Domain\GifId;
use Prex\Gifs\Infrastructure\Http\Request\SaveFavoriteGifRequest;
use Prex\Gifs\Infrastructure\Persistence\Eloquent\FavoriteGifModel;
use Prex\Shared\Domain\Clock;
use Prex\Shared\Domain\ValueObject\UserId;
use Tests\Concerns\FakesGiphy;
use Tests\Concerns\InteractsWithOAuth;
use Tests\TestCase;

final class SaveFavoriteGifTest extends TestCase
{
    use FakesGiphy, InteractsWithOAuth, RefreshDatabase;

    private UserModel $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        Passport::actingAs($this->user);
        $this->fakeGiphyGif('gif-1');
    }

    #[Test]
    public function it_saves_the_favorite_and_returns_it(): void
    {
        $response = $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'gif-1',
            'alias' => 'my favourite cat',
            'user_id' => $this->user->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.gif_id', 'gif-1')
            ->assertJsonPath('data.alias', 'my favourite cat')
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonPath('data.gif.title', 'Funny cat gif-1')
            ->assertJsonStructure(['data' => ['id', 'saved_at']]);

        $this->assertDatabaseHas('favorite_gifs', [
            'id' => $response->json('data.id'),
            'user_id' => $this->user->id,
            'gif_id' => 'gif-1',
            'alias' => 'my favourite cat',
        ]);
    }

    #[Test]
    public function it_also_accepts_the_upper_case_input_of_the_specification(): void
    {
        $this->postJson('/api/v1/gifs/favorites', [
            'GIF_ID' => 'gif-1',
            'ALIAS' => 'my cat',
            'USER_ID' => $this->user->id,
        ])->assertCreated();
    }

    #[Test]
    public function the_same_gif_cannot_be_saved_twice(): void
    {
        $payload = [
            'gif_id' => 'gif-1',
            'alias' => 'my cat',
            'user_id' => $this->user->id,
        ];

        $this->postJson('/api/v1/gifs/favorites', $payload)->assertCreated();

        $this->postJson('/api/v1/gifs/favorites', $payload)
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'favorite_gif_already_saved');

        self::assertSame(1, FavoriteGifModel::query()->count());
    }

    #[Test]
    public function a_user_cannot_save_a_favorite_for_another_user(): void
    {
        $other = $this->createUser('grace@prex.local', name: 'Grace Hopper');

        $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'gif-1',
            'alias' => 'not mine',
            'user_id' => $other->id,
        ])
            ->assertForbidden()
            ->assertJsonPath('error.code', 'favorite_user_mismatch');

        $this->assertDatabaseEmpty('favorite_gifs');
    }

    #[Test]
    public function it_refuses_to_save_a_gif_that_does_not_exist(): void
    {
        $this->fakeGiphyNotFound();

        $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'unknown',
            'alias' => 'ghost',
            'user_id' => $this->user->id,
        ])
            ->assertNotFound()
            ->assertJsonPath('error.code', 'gif_not_found');

        $this->assertDatabaseEmpty('favorite_gifs');
    }

    #[Test]
    public function it_validates_the_payload(): void
    {
        $this->postJson('/api/v1/gifs/favorites', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['errors' => ['gif_id', 'alias', 'user_id']]]);
    }

    #[Test]
    public function it_rejects_an_alias_longer_than_one_hundred_characters(): void
    {
        $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'gif-1',
            'alias' => str_repeat('a', 101),
            'user_id' => $this->user->id,
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'gif-1',
            'alias' => 'my cat',
            'user_id' => 1,
        ])->assertUnauthorized();

        $this->assertDatabaseEmpty('favorite_gifs');
    }

    /**
     * Regression: two concurrent requests both passed the "does it exist?"
     * check and the loser hit the unique index, answering 500. Saving the same
     * pair twice through the repository reproduces that race deterministically.
     */
    #[Test]
    public function the_unique_index_turns_a_lost_race_into_a_conflict(): void
    {
        $repository = $this->app->make(FavoriteGifRepository::class);
        $clock = $this->app->make(Clock::class);

        $first = FavoriteGif::save(
            UserId::fromInt($this->user->id),
            GifId::fromString('gif-1'),
            Alias::fromString('saved first'),
            $clock->now(),
        );

        $second = FavoriteGif::save(
            UserId::fromInt($this->user->id),
            GifId::fromString('gif-1'),
            Alias::fromString('saved a moment later'),
            $clock->now(),
        );

        $repository->save($first);

        $this->expectException(FavoriteGifAlreadySaved::class);

        $repository->save($second);
    }

    #[Test]
    public function it_rejects_a_user_id_that_is_not_a_number(): void
    {
        $this->postJson('/api/v1/gifs/favorites', [
            'gif_id' => 'gif-1',
            'alias' => 'my favourite cat',
            'user_id' => true,
        ])->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['errors' => ['user_id']]]);
    }

    /**
     * Asserted against the rules themselves: `TrimStrings` happens to strip the
     * newline before validation runs, so going through HTTP would prove
     * nothing about the pattern. Without the `/D` modifier `$` matches before
     * a trailing newline and "gif-1\n" is accepted as an id.
     */
    #[Test]
    public function the_gif_id_pattern_does_not_accept_a_trailing_newline(): void
    {
        $rules = (new SaveFavoriteGifRequest)->rules();

        $validator = Validator::make([
            'gif_id' => "gif-1\n",
            'alias' => 'my favourite cat',
            'user_id' => $this->user->id,
        ], $rules);

        self::assertTrue($validator->fails());
        self::assertArrayHasKey('gif_id', $validator->errors()->toArray());
    }
}
