<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);

Route::middleware('JwtMiddleware')->get('/test', function () {
    return "test";
});
