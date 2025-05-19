<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

    Route::middleware('JwtMiddleware')->group(function () {
        Route::get('/test', function () {
            return "test";
        });

        Route::controller(\App\Http\Controllers\UserController::class)->prefix('/base/user')->group(function () {
            Route::get('/get', 'get')->middleware(['permission:read User', 'CheckPermissionsForParamsOfUserRoute']);
        });
    });
});
