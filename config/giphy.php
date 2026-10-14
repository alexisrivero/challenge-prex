<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | GIPHY API
    |--------------------------------------------------------------------------
    |
    | Settings of the external gif catalog. Get a key at
    | https://developers.giphy.com/docs/api/#quick-start-guide
    |
    */

    'base_url' => env('GIPHY_BASE_URL', 'https://api.giphy.com/v1'),

    'api_key' => env('GIPHY_API_KEY', ''),

    // Seconds to wait for the whole request / for the connection to be opened.
    'timeout' => (int) env('GIPHY_TIMEOUT', 10),
    'connect_timeout' => (int) env('GIPHY_CONNECT_TIMEOUT', 5),

    // Attempts (not extra retries) performed on transient failures.
    'retries' => (int) env('GIPHY_RETRIES', 2),
    'retry_delay' => (int) env('GIPHY_RETRY_DELAY_MS', 200),

    // Content rating and language used on searches.
    'rating' => env('GIPHY_RATING', 'g'),
    'language' => env('GIPHY_LANGUAGE', 'en'),

];
