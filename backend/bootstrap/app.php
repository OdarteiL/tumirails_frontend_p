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
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            // For API requests, don't redirect - let exception handler manage
            if ($request->is('api/*') || $request->expectsJson()) {
                throw new \Illuminate\Auth\AuthenticationException();
            }

            return '/login'; // Fallback for web routes
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle unauthenticated users for API requests
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication required. Please provide a valid access token.',
                ], 401);
            }
        });

        $exceptions->respond(function (\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                // Handle authentication exceptions
                if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Authentication required. Please provide a valid access token.',
                    ], 401);
                }

                // Determine status code
                $statusCode = 500;
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $statusCode = $exception->getStatusCode();
                } elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;
                }

                // Handle validation exceptions
                if ($exception instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Validation failed',
                        'errors' => $exception->errors(),
                    ], 422);
                }

                // Handle other exceptions
                return response()->json([
                    'success' => false,
                    'error' => $exception->getMessage() ?: 'An error occurred',
                ], $statusCode);
            }

            return $response;
        });
    })->create();
