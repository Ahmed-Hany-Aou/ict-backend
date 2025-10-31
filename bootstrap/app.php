<?php

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
   ->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(
        at: '*',
        headers: Request::HEADER_X_FORWARDED_FOR |
                 Request::HEADER_X_FORWARDED_HOST |
                 Request::HEADER_X_FORWARDED_PORT |
                 Request::HEADER_X_FORWARDED_PROTO |
                 Request::HEADER_X_FORWARDED_AWS_ELB
    );
})
    
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle ModelNotFoundException (404)
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'meta' => [
                        'timestamp' => now()->toIso8601String(),
                        'api_version' => '1.0',
                    ]
                ], 404);
            }
        });

        // Handle ValidationException (422)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'meta' => [
                        'timestamp' => now()->toIso8601String(),
                        'api_version' => '1.0',
                    ]
                ], 422);
            }
        });

        // Handle AuthenticationException (401)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'meta' => [
                        'timestamp' => now()->toIso8601String(),
                        'api_version' => '1.0',
                    ]
                ], 401);
            }
        });

        // Handle AuthorizationException (403)
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                    'meta' => [
                        'timestamp' => now()->toIso8601String(),
                        'api_version' => '1.0',
                    ]
                ], 403);
            }
        });

        // Handle ThrottleRequestsException (429)
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests',
                    'meta' => [
                        'timestamp' => now()->toIso8601String(),
                        'api_version' => '1.0',
                    ]
                ], 429);
            }
        });

        // Handle all other exceptions (500)
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                $response = [
                    'success' => false,
                    'message' => $statusCode === 500 ? 'Internal server error' : $e->getMessage(),
                    'meta' => [
                        'timestamp' => now()->toIso8601String(),
                        'api_version' => '1.0',
                    ]
                ];

                // Add detailed error info in debug mode
                if (config('app.debug')) {
                    $response['errors'] = [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->take(5)->toArray(),
                    ];
                }

                return response()->json($response, $statusCode);
            }
        });
    })->create();
