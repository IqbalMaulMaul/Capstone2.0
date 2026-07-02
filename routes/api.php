<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\FinanceController;

// Midtrans Webhook Callback
Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handle'])->name('midtrans.callback');

// Auth routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Admin/Owner routes
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/categories', [AdminController::class, 'categories']);
        Route::get('/menus', [AdminController::class, 'menus']);
        Route::get('/rooms', [AdminController::class, 'rooms']);
    });

    // Kitchen routes
    Route::prefix('kitchen')->group(function () {
        Route::get('/orders', [KitchenController::class, 'orders']);
        Route::patch('/orders/{order}/status', [KitchenController::class, 'updateStatus']);
        Route::patch('/menus/{menu}/toggle', [KitchenController::class, 'toggleMenu']);
    });

    // Finance routes
    Route::prefix('finance')->group(function () {
        Route::get('/dashboard', [FinanceController::class, 'dashboard']);
    });
});

