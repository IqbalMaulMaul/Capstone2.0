@extends('guest.layouts.app')

@section('title', 'Lacak Pesanan')
@section('back_url', route('guest.menu.index', $token))

@section('content')
<div class="px-4 py-6 pb-28" x-data="orderTracking({{ $order->id }}, '{{ $order->status }}')">
    
    <!-- Toast Notification -->
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-[-20px]" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-[-20px]" class="fixed top-4 left-4 right-4 z-50 max-w-lg mx-auto" style="display: none;">
        <div class="bg-primary text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3">
            <i class="fa-solid fa-bell text-lg animate-bounce"></i>
            <span class="text-sm font-medium" x-text="toastMessage"></span>
        </div>
    </div>
    
    <!-- Header -->
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-text-primary mb-1">Status Pesanan</h2>
        <p class="text-text-secondary text-sm">No. <span class="font-bold text-text-primary">{{ $order->order_number }}</span></p>
    </div>

    <!-- Status Card -->
    <div class="bg-white rounded-2xl p-6 border border-border relative overflow-hidden mb-8">
        <!-- Background Pattern -->
        <div class="absolute -right-4 -bottom-4 text-primary/5 text-8xl z-0">
            <i class="fa-solid fa-bell-concierge"></i>
        </div>
        
        <div class="relative z-10 flex flex-col items-center">
            <!-- Dynamic Icon based on status -->
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl mb-4 transition-all duration-500" :class="{
                'bg-warning-light text-warning': status === '{{ \App\Models\Order::STATUS_PAID }}' || status === '{{ \App\Models\Order::STATUS_ACCEPTED }}',
                'bg-info-light text-info': status === '{{ \App\Models\Order::STATUS_PROCESSING }}',
                'bg-primary-light/20 text-primary': status === '{{ \App\Models\Order::STATUS_READY }}' || status === '{{ \App\Models\Order::STATUS_DELIVERED }}',
                'bg-success-light text-success': status === '{{ \App\Models\Order::STATUS_COMPLETED }}',
                'bg-danger-light text-danger': status === '{{ \App\Models\Order::STATUS_CANCELLED }}'
            }">
                <i class="fa-solid fa-spinner fa-spin" x-show="status === '{{ \App\Models\Order::STATUS_PAID }}' || status === '{{ \App\Models\Order::STATUS_ACCEPTED }}'"></i>
                <i class="fa-solid fa-fire-burner" x-show="status === '{{ \App\Models\Order::STATUS_PROCESSING }}'"></i>
                <i class="fa-solid fa-person-walking" x-show="status === '{{ \App\Models\Order::STATUS_READY }}' || status === '{{ \App\Models\Order::STATUS_DELIVERED }}'"></i>
                <i class="fa-solid fa-check-circle" x-show="status === '{{ \App\Models\Order::STATUS_COMPLETED }}'"></i>
                <i class="fa-solid fa-times-circle" x-show="status === '{{ \App\Models\Order::STATUS_CANCELLED }}'"></i>
            </div>
            
            <h3 class="text-xl font-bold text-text-primary text-center mb-1.5" x-text="statusLabel"></h3>
            
            <template x-if="status === '{{ \App\Models\Order::STATUS_PAID }}' || status === '{{ \App\Models\Order::STATUS_ACCEPTED }}'">
                <p class="text-sm text-center text-text-secondary">Pesanan Anda telah dibayar dan sedang menunggu dapur.</p>
            </template>
            <template x-if="status === '{{ \App\Models\Order::STATUS_PROCESSING }}'">
                <p class="text-sm text-center text-text-secondary">Koki kami sedang menyiapkan pesanan Anda.</p>
            </template>
            <template x-if="status === '{{ \App\Models\Order::STATUS_READY }}' || status === '{{ \App\Models\Order::STATUS_DELIVERED }}'">
                <p class="text-sm text-center text-text-secondary">Staf kami sedang menuju ke kamar Anda.</p>
            </template>
            <template x-if="status === '{{ \App\Models\Order::STATUS_COMPLETED }}'">
                <p class="text-sm text-center text-success font-medium">Selamat menikmati hidangan Anda!</p>
            </template>
        </div>
    </div>

    <!-- Timeline Progress -->
    <div class="px-2 mb-8">
        <div class="relative">
            <!-- Track Line -->
            <div class="absolute top-0 bottom-0 left-[15px] w-[2px] bg-border z-0"></div>
            <!-- Active Track Line -->
            <div class="absolute top-0 left-[15px] w-[2px] bg-primary z-0 transition-all duration-1000" :style="`height: ${progressHeight}%`"></div>

            <!-- Steps -->
            <div class="space-y-6 relative z-10">
                <!-- Step 1: Paid -->
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors duration-500" :class="stepIndex >= 1 ? 'bg-primary text-white' : 'bg-surface-muted border border-border text-text-muted'">
                        <i class="fa-solid fa-check text-xs"></i>
                    </div>
                    <div class="pt-1.5">
                        <p class="font-semibold text-text-primary text-sm" :class="stepIndex >= 1 ? '' : 'opacity-50'">Pesanan Diterima</p>
                        <p class="text-xs text-text-secondary" x-show="stepIndex >= 1">Pembayaran terverifikasi</p>
                    </div>
                </div>

                <!-- Step 2: Preparing -->
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors duration-500" :class="stepIndex >= 2 ? 'bg-primary text-white' : 'bg-surface-muted border border-border text-text-muted'">
                        <i class="fa-solid fa-fire text-xs"></i>
                    </div>
                    <div class="pt-1.5">
                        <p class="font-semibold text-text-primary text-sm" :class="stepIndex >= 2 ? '' : 'opacity-50'">Sedang Disiapkan</p>
                        <p class="text-xs text-text-secondary" x-show="stepIndex >= 2">Dapur sedang memasak</p>
                    </div>
                </div>

                <!-- Step 3: Delivering -->
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors duration-500" :class="stepIndex >= 3 ? 'bg-primary text-white' : 'bg-surface-muted border border-border text-text-muted'">
                        <i class="fa-solid fa-walking text-xs"></i>
                    </div>
                    <div class="pt-1.5">
                        <p class="font-semibold text-text-primary text-sm" :class="stepIndex >= 3 ? '' : 'opacity-50'">Sedang Diantar</p>
                        <p class="text-xs text-text-secondary" x-show="stepIndex >= 3">Menuju kamar Anda</p>
                    </div>
                </div>

                <!-- Step 4: Completed -->
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors duration-500" :class="stepIndex >= 4 ? 'bg-success text-white' : 'bg-surface-muted border border-border text-text-muted'">
                        <i class="fa-solid fa-flag-checkered text-xs"></i>
                    </div>
                    <div class="pt-1.5">
                        <p class="font-semibold text-text-primary text-sm" :class="stepIndex >= 4 ? '' : 'opacity-50'">Selesai</p>
                        <p class="text-xs text-success" x-show="stepIndex >= 4">Pesanan telah diterima</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Complete Action -->
    <template x-if="status === '{{ \App\Models\Order::STATUS_DELIVERED }}'">
        <div class="mt-6 mb-8">
            <form action="{{ route('guest.tracking.complete', ['token' => $token, 'order' => $order->id]) }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-4 bg-[#10b981] hover:bg-[#059669] text-white rounded-xl font-bold text-base shadow-lg flex items-center justify-center gap-2 transition-transform active:scale-[0.98]">
                    <i class="fa-solid fa-check-double text-lg"></i> Pesanan Sudah Diterima
                </button>
            </form>
            <p class="text-xs text-center text-text-secondary mt-2">Ketuk tombol di atas jika hidangan sudah diantar ke kamar Anda.</p>
        </div>
    </template>

    <!-- Order Summary Toggle -->
    <div x-data="{ expanded: false }" class="bg-white rounded-xl border border-border overflow-hidden">
        <button @click="expanded = !expanded" class="w-full flex items-center justify-between p-4 font-semibold text-sm text-text-primary hover:bg-surface-alt transition-colors">
            <span>Detail Pesanan</span>
            <i class="fa-solid fa-chevron-down transition-transform duration-300 text-text-secondary" :class="expanded ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="expanded" x-collapse>
            <div class="p-4 pt-0 border-t border-border space-y-3 mt-2">
                @foreach($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <div class="flex gap-2">
                            <span class="font-semibold text-text-secondary">{{ $item->quantity }}x</span>
                            <span class="text-text-primary">{{ $item->menu_name }}</span>
                        </div>
                        <span class="font-medium text-text-primary">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="w-full h-px bg-border my-2"></div>
                <div class="flex justify-between text-sm">
                    <span class="text-text-secondary">Subtotal</span>
                    <span class="font-medium text-text-primary">Rp{{ number_format($order->total - $order->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-text-secondary">Pajak</span>
                    <span class="font-medium text-text-primary">Rp{{ number_format($order->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-base font-bold mt-2 pt-2 border-t border-border border-dashed">
                    <span class="text-text-primary">Total Bayar</span>
                    <span class="text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Button -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-border p-4 max-w-lg mx-auto z-40 pb-safe flex gap-3">
        <a href="{{ route('guest.menu.index', $token) }}" class="flex-1 py-3 bg-surface-muted hover:bg-border text-text-primary border border-border rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus text-xs"></i> Pesan Lagi
        </a>
        <a href="{{ route('guest.history', $token) }}" class="flex-1 py-3 bg-primary hover:bg-primary-light text-white rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-xs"></i> Riwayat
        </a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('orderTracking', (orderId, initialStatus) => ({
            status: initialStatus,
            statusLabel: '{{ $order->status_label }}',
            showToast: false,
            toastMessage: '',
            
            get stepIndex() {
                if (this.status === '{{ \App\Models\Order::STATUS_CANCELLED }}') return 0;
                if (this.status === '{{ \App\Models\Order::STATUS_PAID }}' || this.status === '{{ \App\Models\Order::STATUS_ACCEPTED }}') return 1;
                if (this.status === '{{ \App\Models\Order::STATUS_PROCESSING }}') return 2;
                if (this.status === '{{ \App\Models\Order::STATUS_READY }}' || this.status === '{{ \App\Models\Order::STATUS_DELIVERED }}') return 3;
                if (this.status === '{{ \App\Models\Order::STATUS_COMPLETED }}') return 4;
                return 1;
            },
            
            get progressHeight() {
                if (this.status === '{{ \App\Models\Order::STATUS_CANCELLED }}') return 0;
                return (this.stepIndex - 1) * 33.33; // 3 gaps for 4 steps
            },
            
            init() {
                this.startPolling();
            },

            startPolling() {
                const pollUrl = '{{ route("guest.tracking.poll", ["token" => $token, "order" => $order->id]) }}';
                
                const doPoll = () => {
                    fetch(pollUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        if (data.status !== this.status) {
                            this.status = data.status;
                            this.statusLabel = data.status_label;
                            
                            // Vibrate phone
                            if (navigator.vibrate) {
                                navigator.vibrate([100, 50, 100]);
                            }
                            
                            // Show toast notification
                            this.toastMessage = 'Status diperbarui: ' + data.status_label;
                            this.showToast = true;
                            setTimeout(() => { this.showToast = false; }, 4000);
                        }
                    })
                    .catch(err => console.error('Poll error:', err));
                };

                // Poll every 5 seconds
                this._pollTimer = setInterval(doPoll, 5000);

                // Also poll when tab becomes visible
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) doPoll();
                });
            },

            destroy() {
                if (this._pollTimer) clearInterval(this._pollTimer);
            }
        }));
    });
</script>
@endpush
@endsection
