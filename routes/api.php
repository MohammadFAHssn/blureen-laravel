<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

    Route::post('/login-supplier', [\App\Http\Controllers\Auth\AuthController::class, 'loginSupplier']);

    Route::post('/verify-supplier-otp', [\App\Http\Controllers\Auth\AuthController::class, 'verifySupplierOtp']);

    Route::middleware('JwtMiddleware')->group(function () {

        Route::controller(\App\Http\Controllers\Base\BaseController::class)->group(function () {
            Route::get('/get', 'get')->middleware('CheckPermission');
        });

        Route::controller(\App\Http\Controllers\Api\RayvarzController::class)->prefix('/rayvarz')->group(function () {
            Route::post('/sync', 'sync');
        });
    });

    Route::get('/test', function () {
        return 'test';
    });
});
