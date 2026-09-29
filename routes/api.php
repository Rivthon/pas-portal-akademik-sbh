<?php

use App\Http\Controllers\Api\V1\Mahasiswa\AuthController;
use App\Http\Controllers\Api\V1\Mahasiswa\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'PAS API',
        'version' => 'v1',
    ]));

    Route::prefix('mahasiswa')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/dashboard', DashboardController::class);
        });
    });
});
