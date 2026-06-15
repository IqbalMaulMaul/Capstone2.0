@extends('guest.layouts.app')

@section('title', 'Riwayat Pesanan')
@section('back_url', route('guest.menu.index', $token))

@section('content')
<div class="px-4 py-5 pb-24">

    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                <a href="{{ route('guest.tracking.show', ['token' => $token, 'order' => $order->id]) }}" class="block bg-white rounded-xl p-4 border border-border hover:border-primary/30 transition-all group">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="text-xs font-medium text-text-secondary block mb-1">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            <span class="font-bold text-text-primary text-sm">{{ $order->order_number }}</span>
                        </div>
                        
                        <!-- Status Badge -->
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $order->status === \App\Models\Order::STATUS_COMPLETED ? 'bg-success-light text-success' : '' }}
                            {{ $order->status === \App\Models\Order::STATUS_CANCELLED ? 'bg-danger-light text-danger' : '' }}
                            {{ $order->status === \App\Models\Order::STATUS_PENDING_PAYMENT ? 'bg-surface-muted border border-border text-text-secondary' : '' }}
                            {{ in_array($order->status, [\App\Models\Order::STATUS_PAID, \App\Models\Order::STATUS_ACCEPTED, \App\Models\Order::STATUS_PROCESSING, \App\Models\Order::STATUS_READY, \App\Models\Order::STATUS_DELIVERED]) ? 'bg-info-light text-info' : '' }}
                        ">
                            {{ $order->status_label }}
                        </span>
                    </div>
                    
                    <div class="w-full h-px bg-border my-3 border-dashed"></div>
                    
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex -space-x-2">
                            @foreach($order->items->take(3) as $item)
                                <div class="w-8 h-8 rounded-full bg-surface-muted border-2 border-white flex items-center justify-center text-text-muted overflow-hidden text-[10px] font-bold">
                                    @if($item->menu && $item->menu->image_url)
                                        <img src="{{ $item->menu->image_url }}" class="w-full h-full object-cover">
                                    @else
                                        {{ substr($item->menu_name, 0, 1) }}
                                    @endif
                                </div>
                            @endforeach
                            @if($order->items->count() > 3)
                                <div class="w-8 h-8 rounded-full bg-surface-muted text-text-secondary border-2 border-white flex items-center justify-center text-[10px] font-bold">
                                    +{{ $order->items->count() - 3 }}
                                </div>
                            @endif
                        </div>
                        <span class="text-xs text-text-secondary font-medium">{{ $order->items->count() }} Item</span>
                    </div>
                    
                    <div class="flex justify-between items-center mt-1">
                        <span class="font-bold text-primary text-sm">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                        <div class="w-7 h-7 rounded-full bg-surface-muted text-text-secondary flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mx-auto mb-4 text-text-muted">
                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-text-primary mb-1">Belum Ada Pesanan</h3>
            <p class="text-sm text-text-secondary mb-6 max-w-[240px] mx-auto">Anda belum pernah melakukan pemesanan. Yuk pesan sekarang!</p>
            <a href="{{ route('guest.menu.index', $token) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-primary-light transition-all">
                <i class="fa-solid fa-utensils text-xs"></i> Lihat Menu
            </a>
        </div>
    @endif
</div>
@endsection
