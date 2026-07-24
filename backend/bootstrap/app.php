<?php

use App\Http\Middleware\EnsureTenantIsolation;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LogApiActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'tenant' => EnsureTenantIsolation::class,
            'role' => EnsureUserHasRole::class,
            'audit' => LogApiActivity::class,
        ]);

        $middleware->throttleApi(); // rate limiting on all API routes
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true); // API-only app

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token expired.', 'error_code' => 'TOKEN_EXPIRED'], 401);
        });

        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalid.', 'error_code' => 'TOKEN_INVALID'], 401);
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage() ?: 'This action is unauthorized.'], 403);
        });

        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Resource not found.'], 404);
        });

        // Domain-level business rule violations (e.g. expired/used invitations)
        // thrown from the Service layer — mapped to 422 rather than a raw 500.
        $exceptions->render(function (\DomainException $e) {
            return response()->json(['message' => $e->getMessage(), 'error_code' => 'DOMAIN_ERROR'], 422);
        });
    })->create();
