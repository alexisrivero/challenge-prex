<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\ServiceProvider;
use Prex\Audit\Application\RecordServiceInteraction\RecordServiceInteractionHandler;
use Prex\Audit\Domain\ServiceInteractionRepository;
use Prex\Audit\Infrastructure\Http\Middleware\RecordServiceInteraction;
use Prex\Audit\Infrastructure\Persistence\Eloquent\EloquentServiceInteractionRepository;
use Prex\Auth\Domain\AccessTokenIssuer;
use Prex\Auth\Domain\PasswordHasher;
use Prex\Auth\Domain\UserRepository;
use Prex\Auth\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Prex\Auth\Infrastructure\Security\LaravelPasswordHasher;
use Prex\Auth\Infrastructure\Security\PassportAccessTokenIssuer;
use Prex\Gifs\Domain\Favorite\FavoriteGifRepository;
use Prex\Gifs\Domain\GifCatalog;
use Prex\Gifs\Infrastructure\Giphy\GiphyConfiguration;
use Prex\Gifs\Infrastructure\Giphy\GiphyGifCatalog;
use Prex\Gifs\Infrastructure\Giphy\GiphyGifMapper;
use Prex\Gifs\Infrastructure\Persistence\Eloquent\EloquentFavoriteGifRepository;
use Prex\Shared\Domain\Clock;
use Prex\Shared\Infrastructure\Clock\SystemClock;
use Prex\Shared\Infrastructure\Http\AuthenticatedUser;
use Prex\Shared\Infrastructure\Http\Exception\ApiExceptionRenderer;
use Prex\Shared\Infrastructure\Http\RequestUserContext;

/**
 * The composition root of the hexagon.
 *
 * This is the only place where a port (an interface owned by the domain) is
 * wired to an adapter (an implementation owned by the infrastructure). Swapping
 * MariaDB for another storage, or GIPHY for another catalog, means changing one
 * line here - no use case is touched.
 */
final class HexagonalBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerSharedKernel();
        $this->registerAuthContext();
        $this->registerGifsContext();
        $this->registerAuditContext();
    }

    private function registerSharedKernel(): void
    {
        $this->app->singleton(Clock::class, SystemClock::class);

        $this->app->singleton(
            AuthenticatedUser::class,
            fn (): AuthenticatedUser => new AuthenticatedUser(
                $this->app->make('auth'),
                (string) config('auth.defaults.guard', 'api'),
            )
        );

        // Request scoped: shared between the primary adapters and the
        // terminable middleware that writes the audit trail.
        $this->app->singleton(RequestUserContext::class);

        $this->app->singleton(
            ApiExceptionRenderer::class,
            fn (): ApiExceptionRenderer => new ApiExceptionRenderer((bool) config('app.debug', false))
        );
    }

    private function registerAuthContext(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);

        $this->app->bind(
            PasswordHasher::class,
            fn (): PasswordHasher => new LaravelPasswordHasher($this->app->make(Hasher::class))
        );

        $this->app->bind(
            AccessTokenIssuer::class,
            fn (): AccessTokenIssuer => new PassportAccessTokenIssuer(
                $this->app->make(Clock::class),
                (string) config('oauth.token_name', 'api'),
                array_values((array) config('oauth.scopes', [])),
            )
        );
    }

    private function registerGifsContext(): void
    {
        $this->app->singleton(
            GiphyConfiguration::class,
            fn (): GiphyConfiguration => GiphyConfiguration::fromArray((array) config('giphy', []))
        );

        $this->app->bind(
            GifCatalog::class,
            fn (): GifCatalog => new GiphyGifCatalog(
                $this->app->make(HttpClient::class),
                $this->app->make(GiphyGifMapper::class),
                $this->app->make(GiphyConfiguration::class),
            )
        );

        $this->app->bind(FavoriteGifRepository::class, EloquentFavoriteGifRepository::class);
    }

    private function registerAuditContext(): void
    {
        $this->app->bind(ServiceInteractionRepository::class, EloquentServiceInteractionRepository::class);

        $this->app->bind(
            RecordServiceInteraction::class,
            fn (): RecordServiceInteraction => new RecordServiceInteraction(
                $this->app->make(RecordServiceInteractionHandler::class),
                $this->app->make(AuthenticatedUser::class),
                $this->app->make(RequestUserContext::class),
                (bool) config('audit.enabled', true),
            )
        );

        $this->app->bind(
            RecordServiceInteractionHandler::class,
            fn (): RecordServiceInteractionHandler => new RecordServiceInteractionHandler(
                $this->app->make(ServiceInteractionRepository::class),
                $this->app->make(Clock::class),
                array_values((array) config('audit.redacted_keys', [])),
                (int) config('audit.max_payload_bytes', 65535),
            )
        );
    }
}
