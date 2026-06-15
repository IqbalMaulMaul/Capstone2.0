<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Events\OrderStatusUpdated;

class OrderController extends Controller
{
    // For Kitchen Display
    public function kitchen()
    {
        $orders = Order::with(['room', 'items'])
            ->whereIn('status', [
                Order::STATUS_PAID,
                Order::STATUS_ACCEPTED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $categories = \App\Models\Category::with('menus')->orderBy('sort_order')->get();

        return view('admin.orders.kitchen', compact('orders', 'categories'));
    }

    // Kitchen Polling endpoint - returns HTML partial for AJAX
    public function kitchenPoll()
    {
        $orders = Order::with(['room', 'items'])
            ->whereIn('status', [
                Order::STATUS_PAID,
                Order::STATUS_ACCEPTED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $html = view('admin.orders._kitchen_orders', compact('orders'))->render();
        
        return response()->json([
            'html' => $html,
            'count' => $orders->count(),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Order::STATUS_LABELS)),
        ]);

        $order->update(['status' => $request->status]);

        // Broadcast to Guest PWA
        OrderStatusUpdated::dispatch($order);

        return back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    // For Owner: View all orders
    public function index()
    {
        $orders = Order::with(['room', 'payment'])->orderBy('created_at', 'desc')->get();
        return view('admin.orders.index', compact('orders'));
    }

    // For Owner: View order details
    public function show(Order $order)
    {
        $order->load(['room', 'items.menu', 'payment']);
        return view('admin.orders.show', compact('order'));
    }

    // Print Receipt
    public function print(Order $order)
    {
        $order->load(['room', 'items.menu', 'payment']);
        return view('admin.orders.print', compact('order'));
    }
}
