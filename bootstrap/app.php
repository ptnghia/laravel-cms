<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware for all requests (maintenance mode first)
        $middleware->web(prepend: [
            \App\Http\Middleware\MaintenanceMode::class,
        ]);

        // Global middleware for all API requests
        $middleware->api(prepend: [
            \App\Http\Middleware\MaintenanceMode::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\ApiVersioning::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\FormatApiResponse::class,
            \App\Http\Middleware\LogActivity::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'format.api' => \App\Http\Middleware\FormatApiResponse::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,
            'api.version' => \App\Http\Middleware\ApiVersioning::class,
            'rate.limit' => \App\Http\Middleware\CustomRateLimiter::class,
            'log.activity' => \App\Http\Middleware\LogActivity::class,
            'maintenance' => \App\Http\Middleware\MaintenanceMode::class,
        ]);

        // Rate limiting groups
        $middleware->group('auth.limited', [
            'rate.limit:auth',
        ]);

        $middleware->group('api.limited', [
            'rate.limit:api',
        ]);

        $middleware->group('upload.limited', [
            'rate.limit:upload',
        ]);

        $middleware->group('search.limited', [
            'rate.limit:search',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle API exceptions
        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') && $request->expectsJson()) {
                return \App\Exceptions\ApiExceptionHandler::render($request, $e);
            }
        });
    })->create();
