<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Prex\Audit\Domain\Payload;
use Prex\Audit\Infrastructure\Persistence\Eloquent\ServiceInteractionModel;
use Tests\Concerns\FakesGiphy;
use Tests\Concerns\InteractsWithOAuth;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use FakesGiphy, InteractsWithOAuth, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapOAuthServer();
        $this->createUser();
    }

    #[Test]
    public function it_returns_an_oauth2_access_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@prex.local',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'expires_at',
                    'user' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'ada@prex.local');

        self::assertNotEmpty($response->json('data.access_token'));
    }

    #[Test]
    public function the_token_expires_in_thirty_minutes(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@prex.local',
            'password' => 'password',
        ]);

        self::assertSame(30 * 60, $response->json('data.expires_in'));
    }

    #[Test]
    public function the_returned_token_grants_access_to_the_protected_services(): void
    {
        $this->fakeGiphySearch(['gif-1']);

        $token = $this->loginAndGetAccessToken();

        $this->getJson('/api/v1/gifs?query=cats', $this->bearer($token))->assertOk();
    }

    #[Test]
    public function it_rejects_a_wrong_password(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@prex.local',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_credentials');
    }

    #[Test]
    public function it_rejects_an_unknown_email_with_the_same_error(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@prex.local',
            'password' => 'password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'invalid_credentials')
            ->assertJsonPath('error.message', 'The provided credentials are invalid.');
    }

    #[Test]
    public function it_validates_the_payload(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['errors' => ['email', 'password']]]);
    }

    #[Test]
    public function it_rejects_a_malformed_email(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ])->assertUnprocessable();
    }

    #[Test]
    public function the_interaction_is_audited_with_the_credentials_redacted(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@prex.local',
            'password' => 'password',
        ])->assertOk();

        $interaction = ServiceInteractionModel::query()->sole();

        self::assertSame('auth.login', $interaction->service_name);
        self::assertSame('POST', $interaction->http_method);
        self::assertSame('/api/v1/auth/login', $interaction->path);
        self::assertSame(200, $interaction->response_status);
        self::assertSame(1, $interaction->user_id, 'A successful login is attributed to the user.');
        self::assertSame('ada@prex.local', $interaction->request_body['email']);
        self::assertSame(Payload::REDACTED, $interaction->request_body['password']);
        self::assertSame(Payload::REDACTED, $interaction->response_body['data']['access_token']);
        self::assertNotEmpty($interaction->ip_address);
    }

    #[Test]
    public function a_failed_login_is_audited_without_a_user(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'ada@prex.local',
            'password' => 'wrong-password',
        ])->assertUnauthorized();

        $interaction = ServiceInteractionModel::query()->sole();

        self::assertNull($interaction->user_id);
        self::assertSame(401, $interaction->response_status);
    }
}
