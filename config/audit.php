<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Audit trail
    |--------------------------------------------------------------------------
    |
    | Every interaction with the API is persisted (user, service, request body,
    | response status, response body and origin IP).
    |
    */

    'enabled' => (bool) env('AUDIT_ENABLED', true),

    // Payload keys (at any depth) whose value is replaced by "[redacted]".
    'redacted_keys' => [
        'password',
        'password_confirmation',
        'current_password',
        'access_token',
        'refresh_token',
        'token',
        'secret',
        'client_secret',
        'authorization',
        'api_key',
    ],

    // Bodies bigger than this are stored as a truncated preview.
    'max_payload_bytes' => (int) env('AUDIT_MAX_PAYLOAD_BYTES', 65535),

];
