<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function dashboard()
    {
        // ─── Revenue Stats ──────────────────────────────────
        $todayRevenue = Payment::whereDate('paid_at', today())
            ->where('status', Payment::STATUS_SUCCESS)
            ->sum('amount');

        $weekRevenue = Payment::whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', Payment::STATUS_SUCCESS)
            ->sum('amount');

        $monthRevenue = Payment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->where('status', Payment::STATUS_SUCCESS)
            ->sum('amount');

        // ─── Transaction Counts ─────────────────────────────
        $totalTransactions = Payment::where('status', Payment::STATUS_SUCCESS)->count();
        $pendingTransactions = Payment::where('status', Payment::STATUS_PENDING)->count();
        $failedTransactions = Payment::whereIn('status', [Payment::STATUS_FAILED, Payment::STATUS_EXPIRED])->count();

        $averageTransaction = $totalTransactions > 0
            ? Payment::where('status', Payment::STATUS_SUCCESS)->avg('amount')
            : 0;

        // ─── 7-Day Revenue Chart ────────────────────────────
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartData[] = (float) Payment::whereDate('paid_at', $date->toDateString())
                ->where('status', Payment::STATUS_SUCCESS)
                ->sum('amount');
        }

        // ─── Top 5 Menu by Revenue ──────────────────────────
        $topMenus = OrderItem::select('menu_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('order', function ($q) {
                $q->whereHas('payment', function ($p) {
                    $p->where('status', Payment::STATUS_SUCCESS);
                });
            })
            ->groupBy('menu_id')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->with('menu')
            ->get()
            ->map(function ($item) {
                return [
                    'menu_id' => $item->menu_id,
                    'menu_name' => $item->menu->name ?? 'Menu Dihapus',
                    'total_qty' => (int)$item->total_qty,
                    'total_revenue' => (float)$item->total_revenue,
                    'formatted_revenue' => 'Rp' . number_format($item->total_revenue, 0, ',', '.'),
                ];
            });

        // ─── Recent Transactions ────────────────────────────
        $recentPayments = Payment::with(['order.room'])
            ->where('status', Payment::STATUS_SUCCESS)
            ->orderBy('paid_at', 'desc')
            ->take(15)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'order_number' => $p->order->order_number ?? '-',
                    'room_number' => $p->order->room->room_number ?? '-',
                    'amount' => (float)$p->amount,
                    'formatted_amount' => 'Rp' . number_format($p->amount, 0, ',', '.'),
                    'payment_type' => $p->payment_type,
                    'paid_at' => $p->paid_at ? $p->paid_at->toISOString() : null,
                    'paid_at_human' => $p->paid_at ? $p->paid_at->diffForHumans() : '-',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'todayRevenue' => (float)$todayRevenue,
                'weekRevenue' => (float)$weekRevenue,
                'monthRevenue' => (float)$monthRevenue,
                'totalTransactions' => $totalTransactions,
                'pendingTransactions' => $pendingTransactions,
                'failedTransactions' => $failedTransactions,
                'averageTransaction' => (float)$averageTransaction,
                'chart' => [
                    'labels' => $chartLabels,
                    'data' => $chartData,
                ],
                'topMenus' => $topMenus,
                'recentPayments' => $recentPayments,
            ]
        ]);
    }
}
