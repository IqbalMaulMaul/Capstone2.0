<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Room;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $todayOrders = Order::today()->count();
        $pendingOrders = Order::byStatus(Order::STATUS_PAID)->count();
        $kitchenActive = Order::kitchenActive()->count();
        
        $todayRevenue = Payment::whereDate('paid_at', today())
                            ->where('status', Payment::STATUS_SUCCESS)
                            ->sum('amount');

        $recentOrders = Order::with('room')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'room_number' => $order->room->room_number ?? '-',
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'created_at' => $order->created_at->toISOString(),
                    'created_at_human' => $order->created_at->diffForHumans(),
                    'total' => (float)$order->total,
                    'formatted_total' => $order->formatted_total,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'todayOrders' => $todayOrders,
                'pendingOrders' => $pendingOrders,
                'kitchenActive' => $kitchenActive,
                'todayRevenue' => (float)$todayRevenue,
                'recentOrders' => $recentOrders,
            ]
        ]);
    }

    public function categories()
    {
        $categories = Category::withCount('menus')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function menus()
    {
        $menus = Menu::with('category')->get()->map(function ($menu) {
            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'category_name' => $menu->category->name ?? '-',
                'price' => (float)$menu->price,
                'formatted_price' => 'Rp' . number_format($menu->price, 0, ',', '.'),
                'is_available' => $menu->is_available,
                'image_url' => $menu->image ? asset('storage/' . $menu->image) : null,
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    public function rooms()
    {
        $rooms = Room::all()->map(function ($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'is_active' => $room->is_active,
                'qr_token' => $room->qr_token,
                'qr_url' => route('guest.menu.index', ['token' => $room->qr_token]),
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $rooms
        ]);
    }
}
