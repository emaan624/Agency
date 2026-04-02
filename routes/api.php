<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderApiController;
use App\Http\Controllers\Api\V1\WalletApiController;
use App\Http\Controllers\Api\V1\PackageApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/packages', [PackageApiController::class, 'index']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Orders
        Route::get('/orders', [OrderApiController::class, 'index']);
        Route::post('/orders', [OrderApiController::class, 'store']);
        Route::get('/orders/{order}', [OrderApiController::class, 'show']);
        Route::delete('/orders/{order}', [OrderApiController::class, 'cancel']);

        // Wallet
        Route::get('/wallet', [WalletApiController::class, 'index']);

        // Notifications
        Route::get('/notifications', [NotificationApiController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markRead']);
    });
});
