<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guest\MenuController as GuestMenuController;
use App\Http\Controllers\Guest\CartController;


Route::get('/', function () {
    return response()->json(['message' => 'Hotel API is running. Admin interface is now on mobile app.']);
});

// Shortcut for Testing Guest View without needing to scan QR
Route::get('/test-guest', function () {
    $room = \App\Models\Room::firstOrCreate(
        ['room_number' => 'TEST-01'],
        ['floor' => 1, 'is_active' => true, 'qr_token' => 'TEST-TOKEN-123']
    );
    return redirect()->route('guest.menu.index', ['token' => $room->qr_token]);
});

// Guest Routes (QR Code Entry)
Route::prefix('room/{token}')->middleware(['web', 'room.auth'])->name('guest.')->group(function () {
    // Menu
    Route::get('/', [GuestMenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/{slug}', [GuestMenuController::class, 'show'])->name('menu.show');
    
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    
    // Checkout & Payment
    Route::get('/checkout', [\App\Http\Controllers\Guest\CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [\App\Http\Controllers\Guest\CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/payment/{order}', [\App\Http\Controllers\Guest\PaymentController::class, 'index'])->name('payment');
    Route::post('/payment/{order}', [\App\Http\Controllers\Guest\PaymentController::class, 'store'])->name('payment.store');
    
    // Tracking & History
    Route::get('/tracking/{order}', [\App\Http\Controllers\Guest\OrderTrackingController::class, 'show'])->name('tracking.show');
    Route::get('/tracking/{order}/poll', [\App\Http\Controllers\Guest\OrderTrackingController::class, 'poll'])->name('tracking.poll');
    Route::post('/tracking/{order}/complete', [\App\Http\Controllers\Guest\OrderTrackingController::class, 'complete'])->name('tracking.complete');
    Route::get('/history', [\App\Http\Controllers\Guest\OrderTrackingController::class, 'index'])->name('history');
});


