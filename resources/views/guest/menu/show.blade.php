@extends('guest.layouts.app')

@section('title', $menu->name)
@section('back_url', route('guest.menu.index', $token))

@section('content')
<div class="pb-28" x-data="menuDetail()">
    <!-- Menu Image -->
    <div class="w-full aspect-[4/3] bg-surface-muted relative">
        @if($menu->image_url)
            <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-text-muted">
                <i class="fa-solid fa-utensils text-5xl"></i>
            </div>
        @endif
    </div>

    <!-- Menu Info -->
    <div class="px-5 pt-5 pb-4 bg-white relative -mt-4 rounded-t-3xl">
        <div class="flex justify-between items-start gap-4 mb-1">
            <h1 class="text-xl font-bold text-text-primary leading-tight">{{ $menu->name }}</h1>
        </div>
        <p class="text-lg font-bold text-primary mb-4">{{ $menu->formatted_price }}</p>
        
        <div class="w-full h-px bg-border my-4"></div>
        
        <h3 class="text-sm font-semibold text-text-primary mb-2">Deskripsi</h3>
        <p class="text-sm text-text-secondary leading-relaxed mb-5">
            {{ $menu->description ?: 'Tidak ada deskripsi untuk menu ini.' }}
        </p>

        <!-- Notes -->
        <h3 class="text-sm font-semibold text-text-primary mb-2">Catatan <span class="text-text-muted font-normal">(Opsional)</span></h3>
        <textarea x-model="notes" rows="2" placeholder="Contoh: Tidak pakai pedas, tambah es, dll." class="w-full bg-surface-muted border border-border rounded-xl p-3 text-sm text-text-primary placeholder-text-muted focus:border-primary focus:ring-1 focus:ring-primary/20 focus:bg-white transition-all outline-none resize-none" id="menu-notes"></textarea>
    </div>

    <!-- Fixed Bottom Action Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-border p-4 max-w-lg mx-auto z-40 pb-safe">
        <div class="flex items-center gap-3">
            <!-- Quantity Control -->
            <div class="flex items-center bg-surface-muted rounded-xl h-12 border border-border">
                <button @click="if(quantity > 1) quantity--" class="w-11 h-12 flex items-center justify-center text-text-secondary hover:text-primary transition-colors" id="qty-minus">
                    <i class="fa-solid fa-minus text-xs"></i>
                </button>
                <span class="w-8 text-center font-bold text-text-primary text-sm" x-text="quantity" id="qty-display"></span>
                <button @click="quantity++" class="w-11 h-12 flex items-center justify-center text-text-secondary hover:text-primary transition-colors" id="qty-plus">
                    <i class="fa-solid fa-plus text-xs"></i>
                </button>
            </div>

            <!-- Add to Cart Button -->
            <button @click="addToCart" :disabled="isSubmitting" class="flex-1 h-12 bg-primary hover:bg-primary-light text-white rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2 disabled:opacity-60" id="add-to-cart-btn">
                <template x-if="isSubmitting">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </template>
                <template x-if="!isSubmitting">
                    <div class="flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cart-plus text-sm"></i>
                        <span>Tambah <span x-text="'Rp' + new Intl.NumberFormat('id-ID').format({{ $menu->price }} * quantity)"></span></span>
                    </div>
                </template>
            </button>
        </div>
    </div>
    
    <!-- Success Toast Notification -->
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0" class="fixed bottom-24 left-4 right-4 max-w-sm mx-auto bg-primary text-white px-4 py-3 rounded-xl shadow-lg z-50 flex items-center gap-3" style="display: none;" id="success-toast">
        <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center shrink-0">
            <i class="fa-solid fa-check text-sm"></i>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-sm">Ditambahkan!</p>
            <p class="text-xs text-white/80">{{ $menu->name }}</p>
        </div>
        <a href="{{ route('guest.cart', $token) }}" class="text-accent-light text-sm font-bold whitespace-nowrap" id="view-cart-link">Keranjang →</a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('menuDetail', () => ({
            quantity: 1,
            notes: '',
            isSubmitting: false,
            showToast: false,
            
            async addToCart() {
                if (this.isSubmitting) return;
                
                this.isSubmitting = true;
                
                try {
                    const response = await fetch('{{ route('guest.cart.add', $token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            menu_id: {{ $menu->id }},
                            quantity: this.quantity,
                            notes: this.notes
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        window.dispatchEvent(new CustomEvent('cart-updated', { 
                            detail: { count: data.cart_count } 
                        }));
                        
                        this.showToast = true;
                        this.quantity = 1;
                        this.notes = '';
                        
                        setTimeout(() => {
                            this.showToast = false;
                        }, 3000);
                    }
                } catch (error) {
                    console.error('Error adding to cart:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                } finally {
                    this.isSubmitting = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection
