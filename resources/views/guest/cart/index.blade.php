@extends('guest.layouts.app')

@section('title', 'Keranjang')
@section('back_url', route('guest.menu.index', $token))

@section('content')
<div class="px-4 py-5 pb-8">

    @if(count($cart) > 0)
        <!-- Item Count -->
        <p class="text-sm text-text-secondary mb-4">{{ collect($cart)->sum('quantity') }} item di keranjang</p>

        <div class="space-y-3 mb-5">
            @foreach($cart as $id => $item)
                <div class="bg-white rounded-xl p-3.5 flex gap-3.5 border border-border relative" id="cart-item-{{ $id }}">
                    <!-- Remove Button -->
                    <form action="{{ route('guest.cart.remove', $token) }}" method="POST" class="absolute top-2.5 right-2.5">
                        @csrf
                        <input type="hidden" name="menu_id" value="{{ $id }}">
                        <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-full text-text-muted hover:text-danger hover:bg-danger-light transition-colors" onclick="return confirm('Hapus dari keranjang?')" id="remove-{{ $id }}">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </form>

                    <div class="w-16 h-16 rounded-lg bg-surface-muted flex-shrink-0 overflow-hidden">
                        @if($item['image'])
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-text-muted">
                                <i class="fa-solid fa-utensils text-sm"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex-1 flex flex-col min-w-0">
                        <h3 class="font-semibold text-text-primary text-sm leading-tight pr-6 truncate">{{ $item['name'] }}</h3>
                        <p class="text-sm font-bold text-primary mt-0.5">Rp{{ number_format($item['price'], 0, ',', '.') }}</p>
                        
                        @if($item['notes'])
                            <p class="text-xs text-text-muted mt-1 italic truncate"><i class="fa-solid fa-pen text-[10px] mr-1"></i>{{ $item['notes'] }}</p>
                        @endif
                        
                        <div class="mt-auto pt-2.5 flex justify-between items-center">
                            <!-- Quantity Form -->
                            <form action="{{ route('guest.cart.update', $token) }}" method="POST" class="flex items-center bg-surface-muted rounded-lg border border-border">
                                @csrf
                                <input type="hidden" name="menu_id" value="{{ $id }}">
                                <button type="submit" name="action" value="decrease" class="w-8 h-8 flex items-center justify-center text-text-secondary hover:text-primary transition-colors" id="decrease-{{ $id }}">
                                    <i class="fa-solid fa-minus text-[10px]"></i>
                                </button>
                                <span class="w-7 text-center font-bold text-xs text-text-primary">{{ $item['quantity'] }}</span>
                                <button type="submit" name="action" value="increase" class="w-8 h-8 flex items-center justify-center text-text-secondary hover:text-primary transition-colors" id="increase-{{ $id }}">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                </button>
                            </form>

                            <span class="font-bold text-sm text-text-primary">Rp{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Add More Button -->
        <a href="{{ route('guest.menu.index', $token) }}" class="w-full py-3 border border-dashed border-primary/30 rounded-xl flex items-center justify-center gap-2 text-primary font-medium text-sm hover:bg-primary/5 transition-colors mb-6" id="add-more-btn">
            <i class="fa-solid fa-plus text-xs"></i> Tambah menu lain
        </a>

        <!-- Order Summary -->
        <div class="bg-surface-muted rounded-2xl p-5 border border-border">
            <h3 class="font-semibold text-sm text-text-primary mb-4">Ringkasan Pesanan</h3>
            
            <div class="space-y-2.5 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-text-secondary">Subtotal</span>
                    <span class="text-text-primary font-medium">Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-secondary">Pajak ({{ config('hotel.tax_rate', 11) }}%)</span>
                    <span class="text-text-primary font-medium">Rp{{ number_format($tax, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="w-full h-px bg-border my-4"></div>
            
            <div class="flex justify-between items-center mb-5">
                <span class="font-semibold text-text-primary">Total</span>
                <span class="font-bold text-lg text-primary">Rp{{ number_format($total, 0, ',', '.') }}</span>
            </div>
            
            <a href="{{ route('guest.checkout', $token) }}" class="w-full py-3.5 bg-primary hover:bg-primary-light text-white rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2 block text-center" id="checkout-btn">
                Lanjut Checkout <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-surface-muted rounded-full flex items-center justify-center mx-auto mb-4 text-text-muted">
                <i class="fa-solid fa-bag-shopping text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-text-primary mb-1">Keranjang Kosong</h3>
            <p class="text-sm text-text-secondary mb-6 max-w-[240px] mx-auto">Belum ada menu yang dipilih. Yuk lihat menu kami!</p>
            <a href="{{ route('guest.menu.index', $token) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-primary-light transition-all" id="browse-menu-btn">
                <i class="fa-solid fa-utensils text-xs"></i> Lihat Menu
            </a>
        </div>
    @endif
</div>
@endsection
