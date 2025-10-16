<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

    Route::post('/login-supplier', [\App\Http\Controllers\Auth\AuthController::class, 'loginSupplier']);

    Route::post('/verify-supplier-otp', [\App\Http\Controllers\Auth\AuthController::class, 'verifySupplierOtp']);

    Route::post('/get-otp-code', [\App\Http\Controllers\Auth\AuthController::class, 'getOtpCode']);

    Route::controller(\App\Http\Controllers\Commerce\TenderController::class)->prefix('/commerce/tender')->group(function () {
        Route::get('/get-by-token', 'getByToken');
        Route::post('/submit-bid', 'submitBid');
    });

    Route::middleware('JwtMiddleware')->group(function () {
        // Route::controller(\App\Http\Controllers\Api\RayvarzController::class)->prefix('/rayvarz')->group(function () {
        //     Route::post('/sync/{module}/{model_name}', 'sync');
        // });

        // Route::controller(\App\Http\Controllers\Api\KasraController::class)->prefix('/kasra')->group(function () {
        //     Route::post('/sync', 'sync');
        // });

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
            Route::get('/get-the-last-few-months', 'getTheLastFewMonths')->middleware('role:Super Admin|employee');
            // Route::get('print', 'print')->middleware('role:Super Admin|employee');
            Route::get('reports', 'getReports')->middleware('permission:read Payroll-Batches');
        });

        Route::controller(\App\Http\Controllers\PersonnelRecords\PersonnelRecordsController::class)->prefix('/personnel-records')->group(function () {
            Route::get('/get-by-personnel_code', 'getPersonnelRecords')->middleware('permission:read Personnel-Records');
        });

        Route::controller(\App\Http\Controllers\Base\UserController::class)->prefix('/base/user')->group(function () {
            Route::get('/approval-flows-as-requester', 'getApprovalFlowsAsRequester')->middleware('permission:read Approval-Flows');
        });

        Route::controller(\App\Http\Controllers\Base\ApprovalFlowController::class)->prefix('/base/approval-flow')->group(function () {
            Route::post('/update', 'update')->middleware('permission:edit Approval-Flows');
        });

        Route::controller(\App\Http\Controllers\Survey\SurveyController::class)->prefix('/survey/survey')->group(function () {
            Route::post('/create', 'create')->middleware('permission:read Surveys');
            Route::post('/update', 'update')->middleware('permission:read Surveys');
            Route::delete('/', 'delete')->middleware('permission:read Surveys');
            Route::post('/participate', 'participate')->middleware('role:Super Admin|employee');
        });

        //Birthday Routes
        Route::prefix('birthday')->group(function () {
            Route::controller(\App\Http\Controllers\Birthday\BirthdayGiftController::class)->prefix('gift')->group(function () {
                Route::post('/', 'store')->middleware('permission:read Birthdays');
                Route::get('/', 'index')->middleware('permission:read Birthdays');
                Route::get('/get-actives', 'getActives')->middleware('role:Super Admin|employee');
                Route::post('/{id}', 'update')->middleware('permission:read Birthdays');
                Route::delete('/{id}', 'delete')->middleware('permission:read Birthdays');
            });

            Route::controller(\App\Http\Controllers\Birthday\BirthdayFileController::class)->prefix('file')->group(function () {
                Route::post('/', 'store')->middleware('permission:read Birthdays');
                Route::get('/', 'index')->middleware('permission:read Birthdays');
                Route::post('/{id}', 'update')->middleware('permission:read Birthdays');
                Route::delete('/{id}', 'delete')->middleware('permission:read Birthdays');
                Route::get('/statistics', 'statistics')->middleware('permission:read Birthdays');
            });

            Route::controller(\App\Http\Controllers\Birthday\BirthdayFileUserController::class)->prefix('user')->group(function () {
                Route::post('/', 'store')->middleware('permission:read Birthdays');
                Route::get('/', 'index')->middleware('permission:read Birthdays');
                Route::delete('/delete', 'delete')->middleware('permission:read Birthdays');
                Route::post('/status', 'changeStatus')->middleware('permission:read Birthdays');
                Route::post('/choose', 'chooseBirthdayGift')->middleware('role:Super Admin|employee');
                Route::get('/check', 'checkAccess')->middleware('role:Super Admin|employee');
            });
        });

        Route::controller(\App\Http\Controllers\Base\BaseController::class)->group(function () {
            Route::get('/{module}/{model_name}', 'get')->middleware('CheckPermission');
        });
    });

    Route::get('/test', function () {
        return 'test';
    });
});
