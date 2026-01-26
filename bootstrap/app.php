<?php

use App\Jobs\SyncWithKasraJob;
use App\Jobs\SyncWithRayvarzJob;
use Illuminate\Foundation\Application;
use App\Jobs\AssignEmployeeRoleToNewUsersJob;
use App\Jobs\ResolveMealCheckoutTimeJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'JwtMiddleware' => \App\Http\Middleware\JwtMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'CheckPermission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn($request, $e) => $request->is('api/*'));

        $exceptions->render(function (Throwable $e, $request) {
            report($e);

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], $e->status);
            }

            if ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
                return response()->json([
                    'message' => 'شما اجازه انجام این عملیات را ندارید.',
                ], 403);
            }

            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode()
                : (property_exists($e, 'status') ? $e->status
                    : (($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500));
            $statusCode = max(100, min(599, $statusCode));

            if (app()->isLocal() || app()->environment('testing')) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(10),
                ], $statusCode);
            }

            $userMessage = match (true) {
                $e instanceof \Illuminate\Auth\AuthenticationException => 'احراز هویت انجام نشده است.',
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'شما به این عملیات دسترسی ندارید.',
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException,
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException => 'منبع مورد نظر یافت نشد.',
                $e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException => 'روش HTTP نامعتبر است.',
                $statusCode >= 500 => 'خطای سمت سرور.',
                default => $e->getMessage(),
            };

            return response()->json([
                'message' => $userMessage,
            ], $statusCode);
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new SyncWithRayvarzJob('Commerce', 'Supplier', 'supplierId'))->everyFifteenMinutes();
        $schedule->job(new SyncWithKasraJob())->everyFifteenMinutes();
        $schedule->job(new SyncWithRayvarzJob('Base', 'User'))->everyFifteenMinutes();

        $schedule->job(new ResolveMealCheckoutTimeJob())->everyFifteenMinutes();

        // $schedule->job(new AssignEmployeeRoleToNewUsersJob())->everyFifteenMinutes();
    
        $schedule->job(new SyncWithRayvarzJob('Base', 'JobPosition'))->everyFifteenMinutes();
        $schedule->job(new SyncWithRayvarzJob('Base', 'Workplace'))->everyFifteenMinutes();
        $schedule->job(new SyncWithRayvarzJob('Base', 'EducationLevel'))->everyFifteenMinutes();
        $schedule->job(new SyncWithRayvarzJob('Base', 'WorkArea'))->everyFifteenMinutes();
        $schedule->job(new SyncWithRayvarzJob('Base', 'CostCenter'))->everyFifteenMinutes();
    })
    ->create();
