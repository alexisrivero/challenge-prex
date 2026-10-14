<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

/**
 * Framework level configuration: OAuth2 server and API rate limiting.
 */
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configurePassport();
    }

    private function configurePassport(): void
    {
        $accessTokenTtl = (int) config('oauth.access_token_ttl_minutes', 30);
        $refreshTokenTtl = (int) config('oauth.refresh_token_ttl_minutes', 1440);

        // The challenge requires tokens that expire after 30 minutes. Both the
        // tokens issued by our login service (personal access grant) and the
        // ones issued by the standard /oauth/token endpoint share the TTL.
        Passport::tokensExpireIn(now()->addMinutes($accessTokenTtl));
        Passport::personalAccessTokensExpireIn(now()->addMinutes($accessTokenTtl));
        Passport::refreshTokensExpireIn(now()->addMinutes($refreshTokenTtl));

        if ((bool) config('oauth.enable_password_grant', true)) {
            // Keeps the canonical OAuth2 flow available at POST /oauth/token
            // besides our own POST /api/v1/auth/login service.
            Passport::enablePasswordGrant();
        }
    }
}
