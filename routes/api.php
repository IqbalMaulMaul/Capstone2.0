<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentCallbackController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Midtrans Webhook Callback
Route::post('/midtrans/callback', [PaymentCallbackController::class, 'handle'])->name('midtrans.callback');
