<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

    Route::post('/login-supplier', [\App\Http\Controllers\Auth\AuthController::class, 'loginSupplier']);

    Route::post('/verify-supplier-otp', [\App\Http\Controllers\Auth\AuthController::class, 'verifySupplierOtp']);

    Route::controller(\App\Http\Controllers\Commerce\TenderController::class)->prefix('/commerce/tender')->group(function () {
        Route::get('/get-by-token', 'getByToken');
        Route::post('/submit-bid', 'submitBid');
    });

    Route::middleware('JwtMiddleware')->group(function () {
        Route::controller(\App\Http\Controllers\Api\RayvarzController::class)->prefix('/rayvarz')->group(function () {
            // TODO: add middleware
            Route::post('/sync/{module}/{model_name}', 'sync');
        });

        Route::controller(\App\Http\Controllers\Api\KasraController::class)->prefix('/kasra')->group(function () {
            // TODO: add middleware
            Route::post('/sync', 'sync');
        });

        Route::controller(\App\Http\Controllers\Commerce\TenderController::class)->prefix('/commerce/tender')->group(function () {
            Route::get('/get-actives', 'getActives')->middleware('permission:read Active-Tenders');
        });

        Route::controller(\App\Http\Controllers\Base\UserRoleController::class)->prefix('/base/user-role')->group(function () {
            Route::post('/update', 'update')->middleware('permission:edit User-Roles');
        });

        Route::controller(\App\Http\Controllers\Payroll\PayrollBatchController::class)->prefix('/payroll/payroll-batch')->group(function () {
            Route::post('/create', 'create')->middleware('permission:create Payroll-Batch');
            Route::delete('/', 'delete')->middleware('permission:delete Payroll-Batches');
        });

        Route::controller(\App\Http\Controllers\Payroll\PayrollSlipController::class)->prefix('/payroll/payroll-slip')->group(function () {
            Route::get('/get-the-last-few-months', 'getTheLastFewMonths');
        });

        Route::controller(\App\Http\Controllers\PersonnelRecords\PersonnelRecordsController::class)->prefix('/personnel-records')->group(function () {
            Route::get('/get-by-personnel_code', 'getPersonnelRecords')->middleware('permission:read Personnel-Records');
        });

        // TODO: add middleware
        Route::controller(\App\Http\Controllers\Base\UserController::class)->prefix('/base/user')->group(function () {
            Route::get('/approval-flows-as-requester', 'getApprovalFlowsAsRequester');
        });

        // TODO: add middleware
        Route::controller(\App\Http\Controllers\Base\ApprovalFlowController::class)->prefix('/base/approval-flow')->group(function () {
            Route::post('/update', 'update'); //->middleware('permission:Edit Approval-Flows');
        });

        Route::controller(\App\Http\Controllers\Base\BaseController::class)->group(function () {
            Route::get('/{module}/{model_name}', 'get')->middleware('CheckPermission');
        });
    });

    Route::get('/test', function () {
        return 'test';
    });
});
