<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\KitchenController;
use App\Http\Controllers\Api\FinanceController;

// Version check
Route::get('/version', function () {
    return response()->json(['version' => 'debug-v2']);
});

// Midtrans Webhook Callback
Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handle'])->name('midtrans.callback');

// Auth routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Push Token management
    Route::post('/auth/push-token', [AuthController::class, 'storePushToken']);
    Route::delete('/auth/push-token', [AuthController::class, 'deletePushToken']);

    // ─── Admin/Owner routes ─────────────────────────────
    Route::prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Categories CRUD
        Route::get('/categories', [AdminController::class, 'categories']);
        Route::post('/categories', [AdminController::class, 'storeCategory']);
        Route::put('/categories/{category}', [AdminController::class, 'updateCategory']);
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory']);

        // Menus CRUD
        Route::get('/menus', [AdminController::class, 'menus']);
        Route::post('/menus', [AdminController::class, 'storeMenu']);
        Route::post('/menus/{menu}', [AdminController::class, 'updateMenu']); // POST for file upload
        Route::delete('/menus/{menu}', [AdminController::class, 'destroyMenu']);
        Route::patch('/menus/{menu}/toggle', [AdminController::class, 'toggleMenu']);

        // Rooms CRUD
        Route::get('/rooms', [AdminController::class, 'rooms']);
        Route::post('/rooms', [AdminController::class, 'storeRoom']);
        Route::put('/rooms/{room}', [AdminController::class, 'updateRoom']);
        Route::delete('/rooms/{room}', [AdminController::class, 'destroyRoom']);

        // Orders (read-only)
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::get('/orders/{order}', [AdminController::class, 'orderShow']);

        // Payments (read-only)
        Route::get('/payments', [AdminController::class, 'payments']);
    });

    // ─── Kitchen routes ─────────────────────────────────
    Route::prefix('kitchen')->group(function () {
        Route::get('/orders', [KitchenController::class, 'orders']);
        Route::patch('/orders/{order}/status', [KitchenController::class, 'updateStatus']);
        Route::patch('/menus/{menu}/toggle', [KitchenController::class, 'toggleMenu']);
    });

    // ─── Finance routes ─────────────────────────────────
    Route::prefix('finance')->group(function () {
        Route::get('/dashboard', [FinanceController::class, 'dashboard']);
    });
});
