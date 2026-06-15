@extends('admin.layouts.admin')

@section('title', 'Dashboard Keuangan')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard Keuangan</h1>
    <p class="text-gray-600 mt-1">Ringkasan laporan keuangan dan pendapatan.</p>
</div>

<!-- Revenue Stats Grid -->
<div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Pendapatan Hari Ini -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4"></div>
        <div class="relative">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-emerald-100 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-500">Pendapatan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($todayRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Pendapatan Bulan Ini -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4"></div>
        <div class="relative">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-500">Pendapatan Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($monthRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Total Transaksi Berhasil -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-4 -mt-4"></div>
        <div class="relative">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-500">Total Transaksi Berhasil</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalTransactions }}</p>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-xs text-yellow-600 bg-yellow-50 px-2 py-0.5 rounded-full font-medium">{{ $pendingTransactions }} Pending</span>
                    <span class="text-xs text-red-600 bg-red-50 px-2 py-0.5 rounded-full font-medium">{{ $failedTransactions }} Gagal</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rata-rata per Transaksi -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4"></div>
        <div class="relative">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-500">Rata-rata per Transaksi</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($averageTransaction, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart + Top Menus Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- 7-Day Revenue Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Pendapatan 7 Hari Terakhir</h3>
                <p class="text-sm text-gray-500 mt-1">Total minggu ini: Rp{{ number_format($weekRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="p-2 bg-emerald-50 rounded-lg">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
        <div class="relative" style="height: 300px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Top 5 Menu Terlaris -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Top 5 Menu Terlaris</h3>
            <div class="p-2 bg-purple-50 rounded-lg">
                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            </div>
        </div>
        <div class="space-y-4">
            @forelse($topMenus as $index => $item)
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                    {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-100 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500')) }}">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $item->menu->name ?? 'Menu Dihapus' }}</p>
                    <p class="text-xs text-gray-500">{{ $item->total_qty }} terjual</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">Rp{{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-sm text-gray-500 mt-2">Belum ada data penjualan.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Transactions Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
        <a href="{{ route('admin.finance.payments') }}" class="text-sm text-[#c9a84c] hover:text-[#b8973f] font-medium transition-colors">
            Lihat Semua →
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kamar</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recentPayments as $payment)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $payment->transaction_id ?? substr($payment->snap_token ?? '-', 0, 12) . '...' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                        <a href="{{ route('admin.finance.orders.show', $payment->order_id) }}" class="hover:underline">
                            {{ $payment->order->order_number ?? '#' . $payment->order_id }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $payment->order->room->room_number ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ strtoupper($payment->method ?? '-') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-emerald-600">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->paid_at ? $payment->paid_at->format('d M Y H:i') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Belum ada transaksi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($chartData),
                backgroundColor: gradient,
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    titleFont: { family: 'Inter', size: 13 },
                    bodyFont: { family: 'Inter', size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    ticks: {
                        font: { family: 'Inter', size: 11 },
                        color: '#9ca3af',
                        callback: function(value) {
                            if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1) + ' Jt';
                            if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + ' Rb';
                            return 'Rp' + value;
                        }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#9ca3af' }
                }
            }
        }
    });
});
</script>
@endsection
