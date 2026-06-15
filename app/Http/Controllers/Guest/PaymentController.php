<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function index(Request $request, $token, $orderId)
    {
        $order = Order::with('items', 'payment')->where('room_id', $request->room->id)->findOrFail($orderId);

        // Check if order is already paid or cancelled
        if (!$order->isPending()) {
            return redirect()->route('guest.tracking.show', ['token' => $token, 'order' => $order->id]);
        }

        // Generate Snap Token if not exists or if expired
        $payment = $order->payment;
        $snapToken = $payment ? $payment->snap_token : null;

        if (!$payment || ($payment->expired_at && now()->isAfter($payment->expired_at))) {
            try {
                $snapToken = $this->midtransService->createSnapToken($order);
            } catch (\Exception $e) {
                $snapToken = null;
            }
            
            if ($snapToken) {
                if ($payment) {
                    $payment->update([
                        'snap_token' => $snapToken,
                        'expired_at' => now()->addMinutes(config('hotel.payment_expiry', 30)),
                        'status' => Payment::STATUS_PENDING
                    ]);
                } else {
                    Payment::create([
                        'order_id' => $order->id,
                        'snap_token' => $snapToken,
                        'amount' => $order->total,
                        'expired_at' => now()->addMinutes(config('hotel.payment_expiry', 30)),
                        'status' => Payment::STATUS_PENDING
                    ]);
                }
            }
            // If snapToken is null, the view will show the demo/fallback mode
        }

        $snapUrl = config('midtrans.snap_url');
        $clientKey = config('midtrans.client_key');

        return view('guest.payment.index', compact('order', 'snapToken', 'snapUrl', 'clientKey', 'token'));
    }

    public function store(Request $request, $token, $orderId)
    {
        $order = Order::where('room_id', $request->room->id)->findOrFail($orderId);
        
        // Handle demo payment simulation
        if ($request->has('demo_payment')) {
            $payment = $order->payment;
            if (!$payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'transaction_id' => 'DEMO-' . strtoupper(\Illuminate\Support\Str::random(10)),
                    'amount' => $order->total,
                    'status' => Payment::STATUS_SUCCESS,
                    'method' => 'qris',
                    'paid_at' => now(),
                ]);
            } else {
                $payment->update([
                    'transaction_id' => 'DEMO-' . strtoupper(\Illuminate\Support\Str::random(10)),
                    'status' => Payment::STATUS_SUCCESS,
                    'method' => 'qris',
                    'paid_at' => now(),
                ]);
            }
            
            $order->update(['status' => Order::STATUS_PAID]);
            
            \App\Events\OrderStatusUpdated::dispatch($order);
            
            return redirect()->route('guest.tracking.show', ['token' => $token, 'order' => $order->id])
                ->with('success', 'Pembayaran berhasil! (Mode Demo)');
        }

        // Normal flow — check Midtrans status
        if ($order->isPending()) {
            $status = $this->midtransService->getTransactionStatus($order->order_number);
        }

        return redirect()->route('guest.tracking.show', ['token' => $token, 'order' => $order->id]);
    }
}
