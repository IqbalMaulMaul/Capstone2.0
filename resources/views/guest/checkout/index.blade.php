@extends('guest.layouts.app')

@section('title', 'Checkout')
@section('back_url', route('guest.cart', $token))

@section('content')
<div class="px-4 py-5 pb-44">
    <!-- Room Info Card -->
    <div class="bg-primary rounded-2xl p-4 text-white mb-5 relative overflow-hidden">
        <div class="absolute -right-3 -bottom-3 text-white/5 text-7xl">
            <i class="fa-solid fa-location-dot"></i>
        </div>
        <div class="relative z-10">
            <p class="text-xs text-white/70 mb-0.5">Pengantaran ke</p>
            <p class="text-xl font-bold">Kamar {{ session('room_number') }}</p>
            <p class="text-xs text-white/60 mt-1"><i class="fa-regular fa-clock mr-1"></i>Estimasi {{ $estimatedDelivery }} menit</p>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-xl p-4 border border-border mb-4">
        <h3 class="font-semibold text-sm text-text-primary mb-3 pb-3 border-b border-border">Daftar Pesanan</h3>
        <div class="space-y-3">
            @foreach($cart as $item)
                <div class="flex justify-between items-start text-sm">
                    <div class="flex gap-2.5">
                        <span class="text-text-secondary font-medium w-5 text-right shrink-0">{{ $item['quantity'] }}x</span>
                        <div>
                            <p class="text-text-primary font-medium leading-tight">{{ $item['name'] }}</p>
                            @if($item['notes'])
                                <p class="text-xs text-text-muted mt-0.5 italic">{{ $item['notes'] }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="font-medium text-text-primary whitespace-nowrap ml-3">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Checkout Form -->
    <form action="{{ route('guest.checkout.store', $token) }}" method="POST" id="checkout-form">
        @csrf
        
        <div class="bg-white rounded-xl p-4 border border-border mb-4">
            <h3 class="font-semibold text-sm text-text-primary mb-3 pb-3 border-b border-border">Informasi Tambahan</h3>
            
            <div class="space-y-4">
                <div>
                    <label for="guest_name" class="block text-sm font-medium text-text-primary mb-1.5">Nama Pemesan <span class="text-text-muted font-normal">(Opsional)</span></label>
                    <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" placeholder="Atas nama..." class="w-full bg-surface-muted border border-border rounded-xl py-2.5 px-3.5 text-sm text-text-primary placeholder-text-muted focus:border-primary focus:ring-1 focus:ring-primary/20 focus:bg-white transition-all outline-none">
                </div>
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-text-primary mb-1.5">Catatan <span class="text-text-muted font-normal">(Opsional)</span></label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Contoh: Tolong antar setelah jam 7 malam." class="w-full bg-surface-muted border border-border rounded-xl py-2.5 px-3.5 text-sm text-text-primary placeholder-text-muted focus:border-primary focus:ring-1 focus:ring-primary/20 focus:bg-white transition-all outline-none resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Fixed Bottom Bar -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-border p-4 max-w-lg mx-auto z-40 pb-safe">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <p class="text-xs text-text-secondary">Total Pembayaran</p>
                    <p class="text-xl font-bold text-primary mt-0.5">Rp{{ number_format($total, 0, ',', '.') }}</p>
                </div>
                <span class="text-[11px] text-text-muted">Termasuk pajak {{ config('hotel.tax_rate', 11) }}%</span>
            </div>
            
            <button type="submit" class="w-full h-12 bg-primary hover:bg-primary-light text-white rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2" id="pay-now-btn" onclick="this.disabled=true; this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Memproses...'; document.getElementById('checkout-form').submit();">
                <i class="fa-solid fa-lock text-xs"></i> Bayar Sekarang
            </button>
        </div>
    </form>
</div>
@endsection
