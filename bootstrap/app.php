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
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $response = response()->json([
                    'message' => $e->getMessage(),
                    'code' => method_exists($e, 'getCode') ? $e->getCode() : 500,
                ], $e->getCode() ?: 500);

                $response->headers->set('Access-Control-Allow-Origin', env('FRONTEND_URL', '*'));
                $response->headers->set('Access-Control-Allow-Credentials', 'true');

                return $response;
            }

            // سایر خطاها: بذار به handler پیش‌فرض بره
            return null;
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new SyncWithRayvarz('supplier', 'supplierId'))
            ->daily();
    })
    ->create();
