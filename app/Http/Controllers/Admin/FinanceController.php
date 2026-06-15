<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            $chartLabels[] = $date->translatedFormat('d M');
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
            ->get();

        // ─── Recent Transactions ────────────────────────────
        $recentPayments = Payment::with(['order.room'])
            ->where('status', Payment::STATUS_SUCCESS)
            ->orderBy('paid_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.finance.dashboard', compact(
            'todayRevenue',
            'weekRevenue',
            'monthRevenue',
            'totalTransactions',
            'pendingTransactions',
            'failedTransactions',
            'averageTransaction',
            'chartLabels',
            'chartData',
            'topMenus',
            'recentPayments'
        ));
    }
}
