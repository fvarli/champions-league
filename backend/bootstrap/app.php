<?php

use App\Exceptions\Contracts\ProvidesHttpStatus;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Force JSON before routing so even unmatched /api/* errors stay JSON.
        $middleware->prepend(ForceJsonResponse::class);

        // Correlation id and security headers wrap every response.
        $middleware->append(RequestIdMiddleware::class);
        $middleware->append(SecurityHeadersMiddleware::class);

        // Rate limit the API: 60 requests per minute per IP (see AppServiceProvider).
        $middleware->appendToGroup('api', 'throttle:api');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ProvidesHttpStatus $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->httpStatus());
            }

            return null;
        });
    })->create();
