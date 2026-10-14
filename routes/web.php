<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| This project is an API: the root route only advertises what is available.
|
*/

Route::get('/', fn (): JsonResponse => new JsonResponse([
    'name' => config('app.name'),
    'description' => 'Challenge PHP Hexagonal - GIFs API',
    'documentation' => 'See README.md and the Postman collection under /postman',
    'endpoints' => [
        'POST /api/v1/auth/login' => 'Authenticate and obtain an OAuth2 access token',
        'GET /api/v1/gifs' => 'Search gifs by phrase or term',
        'GET /api/v1/gifs/{id}' => 'Get one gif by id',
        'POST /api/v1/gifs/favorites' => 'Save a gif as favorite for a user',
    ],
]));
