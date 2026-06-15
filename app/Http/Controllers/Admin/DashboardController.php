<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $todayOrders = Order::today()->count();
        $pendingOrders = Order::byStatus(Order::STATUS_PAID)->count();
        $kitchenActive = Order::kitchenActive()->count();
        
        $todayRevenue = Payment::whereDate('paid_at', today())
                            ->where('status', Payment::STATUS_SUCCESS)
                            ->sum('amount');

        $recentOrders = Order::with('room')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'todayOrders', 'pendingOrders', 'kitchenActive', 'todayRevenue', 'recentOrders'
        ));
    }
}
