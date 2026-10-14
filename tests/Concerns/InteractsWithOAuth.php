<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Hash;
use Prex\Auth\Infrastructure\Persistence\Eloquent\UserModel;
use Tests\TestCase;

/**
 * Prepares the OAuth2 server for the feature tests that issue real tokens.
 *
 * The signing keys are handled once per run by {@see TestCase}; what is
 * needed per test - because the database is refreshed - are the OAuth2 clients.
 */
trait InteractsWithOAuth
{
    protected function bootstrapOAuthServer(): void
    {
        $this->artisan('oauth:ensure-clients')->run();
    }

    protected function createUser(
        string $email = 'ada@prex.local',
        string $password = 'password',
        string $name = 'Ada Lovelace',
    ): UserModel {
        return UserModel::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Performs a real login through the API and returns the access token.
     */
    protected function loginAndGetAccessToken(string $email = 'ada@prex.local', string $password = 'password'): string
    {
        return (string) $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => $password,
        ])->json('data.access_token');
    }

    /**
     * @return array<string, string>
     */
    protected function bearer(string $token): array
    {
        return ['Authorization' => 'Bearer '.$token];
    }
}
