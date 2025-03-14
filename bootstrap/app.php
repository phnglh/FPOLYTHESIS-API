<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport(
            \LaravelJsonApi\Core\Exceptions\JsonApiException::class,
        );

        $exceptions->render(
            \LaravelJsonApi\Exceptions\ExceptionParser::renderer(),
        );

        // ✅ Custom render API Exception
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                if ($e instanceof MethodNotAllowedHttpException) {
                    return response()->json([
                        'status' => 'error',
                        'status_code' => 405,
                        'message' => 'Method not allowed',
                        'error_code' => 'METHOD_NOT_ALLOWED',
                        'data' => null,
                        'errors' => [],
                        'meta' => null,
                        'timestamp' => now()->timestamp,
                    ], 405);
                }

                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'status' => 'error',
                        'status_code' => 404,
                        'message' => 'Route not found',
                        'error_code' => 'ROUTE_NOT_FOUND',
                        'data' => null,
                        'errors' => [],
                        'meta' => null,
                        'timestamp' => now()->timestamp,
                    ], 404);
                }

                if ($e instanceof ValidationException) {
                    return response()->json([
                        'status' => 'error',
                        'status_code' => 422,
                        'message' => 'Validation failed',
                        'error_code' => 'VALIDATION_ERROR',
                        'data' => null,
                        'errors' => $e->errors(),
                        'meta' => null,
                        'timestamp' => now()->timestamp,
                    ], 422);
                }

                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'status' => 'error',
                        'status_code' => 401,
                        'message' => 'Unauthenticated',
                        'error_code' => 'UNAUTHENTICATED',
                        'data' => null,
                        'errors' => [],
                        'meta' => null,
                        'timestamp' => now()->timestamp,
                    ], 401);
                }

                return response()->json([
                    'status' => 'error',
                    'status_code' => $e instanceof HttpException ? $e->getStatusCode() : 500,
                    'message' => $e->getMessage() ?: 'Server Error',
                    'error_code' => 'SERVER_ERROR',
                    'data' => null,
                    'errors' => [],
                    'meta' => null,
                    'timestamp' => now()->timestamp,
                ], $e instanceof HttpException ? $e->getStatusCode() : 500);
            }

            return null;
        });
    })->create();
