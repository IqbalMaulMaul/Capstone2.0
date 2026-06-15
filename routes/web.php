<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guest\MenuController as GuestMenuController;
use App\Http\Controllers\Guest\CartController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\FinanceController;

Route::get('/', function () {
    return view('welcome');
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

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Redirect /admin to login or dashboard
    Route::get('/', function () {
        return redirect()->route('admin.login');
    });
    
    // Guest Admin Routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    // Protected Admin Routes
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Owner only routes
        Route::middleware(['role:owner'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
            Route::resource('menus', AdminMenuController::class);
            Route::get('rooms/{room}/print', [RoomController::class, 'print'])->name('rooms.print');
            Route::resource('rooms', RoomController::class);
            
            // Payments
            Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
            
            // Orders History (Owner)
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        });

        // Kitchen & Owner routes
        Route::middleware(['role:owner,kitchen'])->group(function () {
            Route::get('/kitchen/orders', [OrderController::class, 'kitchen'])->name('kitchen.index');
            Route::get('/kitchen/orders/poll', [OrderController::class, 'kitchenPoll'])->name('kitchen.poll');
            Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
            Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
            
            // Toggle Menu Availability
            Route::patch('/menus/{menu}/toggle', [\App\Http\Controllers\Admin\MenuController::class, 'toggleAvailability'])->name('menus.toggle');
        });

        // Finance & Owner routes
        Route::middleware(['role:owner,finance'])->name('finance.')->prefix('finance')->group(function () {
            Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
            Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments');
            Route::get('/orders', [OrderController::class, 'index'])->name('orders');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        });
    });
});
