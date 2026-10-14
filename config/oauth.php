<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | OAuth2 access tokens
    |--------------------------------------------------------------------------
    |
    | The challenge requires the login token to expire after 30 minutes.
    |
    */

    'access_token_ttl_minutes' => (int) env('OAUTH_ACCESS_TOKEN_TTL_MINUTES', 30),

    'refresh_token_ttl_minutes' => (int) env('OAUTH_REFRESH_TOKEN_TTL_MINUTES', 60 * 24),

    // Name given to the tokens issued by the login service.
    'token_name' => env('OAUTH_TOKEN_NAME', 'api'),

    // Scopes granted on login (empty = full access for the authenticated user).
    'scopes' => [],

    // Enables the standard OAuth2 `password` grant on POST /oauth/token.
    'enable_password_grant' => (bool) env('OAUTH_ENABLE_PASSWORD_GRANT', true),

];
