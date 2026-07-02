<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Category;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function orders()
    {
        $orders = Order::with(['room', 'items.menu'])
            ->whereIn('status', [
                Order::STATUS_PAID,
                Order::STATUS_ACCEPTED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'room_number' => $order->room->room_number ?? '-',
                    'guest_name' => $order->guest_name,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at->toISOString(),
                    'created_at_human' => $order->created_at->diffForHumans(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'menu_name' => $item->menu->name ?? 'Menu Dihapus',
                            'quantity' => $item->quantity,
                            'notes' => $item->notes,
                        ];
                    }),
                ];
            });

        $categories = Category::with('menus')->orderBy('sort_order')->get()->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'menus' => $cat->menus->map(function ($menu) {
                    return [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'is_available' => $menu->is_available,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders,
                'categories' => $categories,
            ]
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Order::STATUS_LABELS)),
        ]);

        $order->update(['status' => $request->status]);

        // Broadcast to Guest PWA
        try {
            OrderStatusUpdated::dispatch($order);
        } catch (\Exception $e) {
            // Silence broadcast error if Pusher/Reverb not running
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status pesanan berhasil diperbarui',
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'status_label' => $order->status_label,
            ]
        ]);
    }

    public function toggleMenu(Request $request, Menu $menu)
    {
        $menu->update([
            'is_available' => !$menu->is_available
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ketersediaan menu berhasil diperbarui',
            'data' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'is_available' => $menu->is_available,
            ]
        ]);
    }
}
