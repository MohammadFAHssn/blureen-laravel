<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\SyncWithRayvarz;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'JwtMiddleware' => \App\Http\Middleware\JwtMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            'CheckPermission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Always return JSON for API routes
        $exceptions->shouldRenderJsonWhen(fn($request, $e) => $request->is('api/*'));

        $exceptions->render(function (Throwable $e, $request) {
            // Report every exception
            report($e);

            // Handle validation exceptions first
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], $e->status);
            }

            // Determine appropriate HTTP status code
            $statusCode = match (true) {
                $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface => $e->getStatusCode(),
                property_exists($e, 'status') => $e->status,
                $e->getCode() >= 100 && $e->getCode() < 600 => $e->getCode(),
                default => 500,
            };
            $statusCode = max(100, min(599, $statusCode));

            // Local / testing environment: verbose debug info
            if (app()->isLocal() || app()->environment('testing')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(10)->all(),
                ], $statusCode);
            }

            // Production: user-friendly messages only
            $userMessage = match (true) {
                $e instanceof \Illuminate\Auth\AuthenticationException => 'Unauthenticated.',
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'Unauthorized action.',
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException,
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
                => 'Resource not found.',
                $e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
                => 'Method not allowed.',
                $statusCode >= 500 => 'Server error.',
                default => $e->getMessage(),
            };

            return response()->json(
                ['message' => $userMessage],
                $statusCode
            );
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new SyncWithRayvarz('supplier', 'supplierId'))
            ->everyThreeMinutes();
        $schedule->job(new SyncWithRayvarz('user', ''))
            ->everyThreeMinutes();
    })
    ->create();
