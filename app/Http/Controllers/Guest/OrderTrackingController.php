<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index($token)
    {
        $room = request()->room; // injected by ValidateRoomToken
        
        $orders = Order::where('room_id', $room->id)
            ->with(['items'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('guest.tracking.history', compact('orders', 'token'));
    }

    public function show($token, $orderId)
    {
        $room = request()->room; // injected by ValidateRoomToken
        
        $order = Order::where('room_id', $room->id)
            ->with(['items', 'payment'])
            ->findOrFail($orderId);

        // If payment is pending, redirect to payment page
        if ($order->status === Order::STATUS_PENDING_PAYMENT) {
            return redirect()->route('guest.payment', ['token' => $token, 'order' => $order->id]);
        }

        return view('guest.tracking.show', compact('order', 'token'));
    }

    public function poll($token, $orderId)
    {
        $room = request()->room; // injected by ValidateRoomToken
        
        $order = Order::where('room_id', $room->id)
            ->findOrFail($orderId);

        return response()->json([
            'status' => $order->status,
            'status_label' => $order->status_label,
        ]);
    }

    public function complete($token, $orderId)
    {
        $room = request()->room; // injected by ValidateRoomToken
        
        $order = Order::where('room_id', $room->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->findOrFail($orderId);

        $order->update(['status' => Order::STATUS_COMPLETED]);

        \App\Events\OrderStatusUpdated::dispatch($order);

        return redirect()->route('guest.tracking.show', ['token' => $token, 'order' => $order->id])
            ->with('success', 'Terima kasih, pesanan Anda telah selesai!');
    }
}
