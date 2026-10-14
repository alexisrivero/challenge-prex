<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Prex\Audit\Infrastructure\Http\Middleware\RecordServiceInteraction;
use Prex\Auth\Infrastructure\Console\EnsureOAuthClientsCommand;
use Prex\Shared\Infrastructure\Http\Exception\ApiExceptionRenderer;
use Prex\Shared\Infrastructure\Http\Middleware\ForceJsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        EnsureOAuthClientsCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Outermost first: the audit middleware wraps the whole API pipeline so
        // it observes the very response that reaches the client.
        $middleware->api(prepend: [
            ForceJsonResponse::class,
            RecordServiceInteraction::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(
            fn (Throwable $exception, Request $request) => app(ApiExceptionRenderer::class)
                ->render($exception, $request)
        );
    })->create();
