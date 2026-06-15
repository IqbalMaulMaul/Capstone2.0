@extends('admin.layouts.admin')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 inline-block">&larr; Kembali ke Riwayat Pesanan</a>
        <h1 class="text-2xl font-bold text-gray-900">Pesanan #{{ $order->order_number }}</h1>
        <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#c9a84c] shadow-sm transition-colors">
            <i class="fa-solid fa-print mr-2"></i> Cetak Struk
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kolom Kiri: Detail Pesanan & Item -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">
                Item Pesanan
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-4">
                            @if($item->menu && $item->menu->image)
                                <img src="{{ asset('storage/' . $item->menu->image) }}" alt="{{ $item->menu_name }}" class="w-16 h-16 object-cover rounded-lg">
                            @else
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $item->menu_name }}</h4>
                                <p class="text-sm text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->menu_price, 0, ',', '.') }}</p>
                                @if($item->notes)
                                <p class="text-xs text-gray-500 mt-1 italic"><i class="fa-solid fa-note-sticky mr-1"></i> "{{ $item->notes }}"</p>
                                @endif
                            </div>
                        </div>
                        <div class="font-medium text-gray-900">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-gray-50 p-6 space-y-3">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Pajak ({{ config('hotel.tax_rate', 11) }}%)</span>
                    <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-3 border-t border-gray-200">
                    <span>Total Keseluruhan</span>
                    <span class="text-[#c9a84c]">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Info Customer & Pembayaran -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">
                Informasi Pelanggan
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Nama Tamu</p>
                    <p class="font-medium text-gray-900">{{ $order->guest_name ?? 'Tidak ada nama' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Nomor Kamar</p>
                    <p class="font-medium text-gray-900">Kamar {{ $order->room->room_number }}</p>
                </div>
                @if($order->notes)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Catatan Pesanan</p>
                    <p class="text-sm text-gray-700 bg-yellow-50 p-3 rounded-lg border border-yellow-100">{{ $order->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">
                Status Pembayaran & Pesanan
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Status Pesanan</p>
                    @php
                        $colors = [
                            'pending_payment' => 'bg-yellow-100 text-yellow-800',
                            'paid' => 'bg-blue-100 text-blue-800',
                            'accepted' => 'bg-indigo-100 text-indigo-800',
                            'processing' => 'bg-purple-100 text-purple-800',
                            'ready' => 'bg-orange-100 text-orange-800',
                            'delivered' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                        ];
                        $colorClass = $colors[$order->status] ?? 'bg-gray-100 text-gray-800';
                        $statusLabels = \App\Models\Order::STATUS_LABELS;
                    @endphp
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $colorClass }}">
                        {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                    </span>
                </div>
                
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Status Pembayaran</p>
                    @if($order->payment)
                        @php
                            $payColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'success' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'expired' => 'bg-gray-100 text-gray-800',
                            ];
                            $payClass = $payColors[$order->payment->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $payClass }}">
                            {{ ucfirst($order->payment->status) }}
                        </span>
                        
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Metode:</span>
                                <span class="font-medium text-gray-900">{{ strtoupper($order->payment->method ?? '-') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Trx ID:</span>
                                <span class="font-medium text-gray-900 break-all text-right ml-4">{{ $order->payment->transaction_id ?? '-' }}</span>
                            </div>
                            @if($order->payment->paid_at)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dibayar pada:</span>
                                <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($order->payment->paid_at)->format('d/m/Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    @else
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                            Belum Ada Data Pembayaran
                        </span>
                    @endif
                </div>
                
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Estimasi Pengantaran</p>
                    <p class="font-medium text-gray-900">
                        {{ $order->estimated_delivery ? \Carbon\Carbon::parse($order->estimated_delivery)->format('d M Y H:i') : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
