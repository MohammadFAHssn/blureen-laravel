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
        // $middleware->prependToGroup('web', \App\Http\Middleware\EncryptCookies::class);
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);

        $middleware->api(prepend: [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
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
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new SyncWithRayvarz('supplier', 'supplierId'))
            ->daily();
    })
    ->create();
