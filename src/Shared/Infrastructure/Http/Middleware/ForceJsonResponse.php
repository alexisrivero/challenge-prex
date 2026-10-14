<?php

declare(strict_types=1);

namespace Prex\Shared\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The API only speaks JSON, so every request is treated as such even when the
 * client forgets the `Accept` header. This keeps validation and error payloads
 * consistent instead of falling back to HTML responses.
 */
final class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
