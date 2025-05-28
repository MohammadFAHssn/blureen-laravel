<?php

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

    Route::middleware('JwtMiddleware')->group(function () {

        Route::controller(\App\Http\Controllers\Base\UserController::class)->prefix('/base/user')->group(function () {
            Route::get('/get', 'get')->middleware('CheckPermission');
        });

        Route::controller(\App\Http\Controllers\Api\RayvarzController::class)->prefix('/rayvarz')->group(function () {
            Route::post('/sync', 'Sync');
        });
    });

    Route::get('/test', function () {
        return "test";
    });
});
