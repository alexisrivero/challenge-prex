<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Prex\Auth\Infrastructure\Http\Controller\LoginController;
use Prex\Gifs\Infrastructure\Http\Controller\FindGifController;
use Prex\Gifs\Infrastructure\Http\Controller\SaveFavoriteGifController;
use Prex\Gifs\Infrastructure\Http\Controller\SearchGifsController;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
|
| The four services required by the challenge. Every route points at a single
| action controller which only translates HTTP <-> use case.
|
*/

Route::prefix('v1')->group(function (): void {
    // Public: exchanges credentials for an OAuth2 access token.
    Route::post('/auth/login', LoginController::class)->name('auth.login');

    // Everything else requires a valid bearer token.
    Route::middleware('auth:api')->group(function (): void {
        Route::get('/gifs', SearchGifsController::class)->name('gifs.search');
        Route::post('/gifs/favorites', SaveFavoriteGifController::class)->name('gifs.favorites.store');
        Route::get('/gifs/{gifId}', FindGifController::class)
            ->where('gifId', '[A-Za-z0-9_-]+')
            ->name('gifs.show');
    });
});
