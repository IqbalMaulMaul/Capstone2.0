@extends('guest.layouts.app')

@section('title', 'Pembayaran')

@section('content')
<div class="px-4 py-6">
    
    <!-- Order Summary Card -->
    <div class="bg-white rounded-xl p-4 border border-border mb-5">
        <div class="flex justify-between items-start mb-3">
            <div>
                <p class="text-xs text-text-secondary">No. Pesanan</p>
                <p class="font-bold text-sm text-text-primary">{{ $order->order_number }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-warning-light text-warning" id="payment-status-badge">
                Menunggu Pembayaran
            </span>
        </div>
        
        <div class="w-full h-px bg-border my-3"></div>
        
        <div class="flex justify-between items-center">
            <span class="text-sm text-text-secondary">Total Tagihan</span>
            <span class="text-lg font-bold text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($snapToken)
        <!-- Payment Instructions -->
        <div class="bg-surface-muted rounded-xl p-4 border border-border mb-5">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-info-light text-info rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-info text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-text-primary mb-1">Cara Pembayaran</p>
                    <p class="text-xs text-text-secondary leading-relaxed">Klik tombol di bawah untuk memilih metode pembayaran. Anda bisa menggunakan QRIS, Transfer Bank, GoPay, dan lainnya.</p>
                </div>
            </div>
        </div>

        <!-- Pay Button -->
        <button id="pay-button" class="w-full py-3.5 bg-primary hover:bg-primary-light text-white rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-2 mb-4">
            <i class="fa-solid fa-wallet"></i> Pilih Metode Pembayaran
        </button>

        <!-- Timer -->
        <p class="text-center text-xs text-text-muted">
            <i class="fa-regular fa-clock mr-1"></i>Selesaikan pembayaran sebelum waktu habis
        </p>
    @else
        <!-- Payment Error / Demo Mode -->
        <div class="text-center py-8">
            <div class="w-14 h-14 bg-warning-light rounded-full flex items-center justify-center mx-auto mb-4 text-warning">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-text-primary mb-2">Gateway Pembayaran Belum Dikonfigurasi</h3>
            <p class="text-sm text-text-secondary mb-6 max-w-[280px] mx-auto leading-relaxed">
                Midtrans belum dikonfigurasi. Untuk keperluan demo, Anda bisa mensimulasikan pembayaran berhasil.
            </p>
            
            <!-- Demo: Simulate Payment Success -->
            <form action="{{ route('guest.payment.store', ['token' => $token, 'order' => $order->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="demo_payment" value="1">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-primary-light transition-all" id="demo-pay-btn">
                    <i class="fa-solid fa-play text-xs"></i> Simulasi Pembayaran Berhasil
                </button>
            </form>

            <a href="{{ route('guest.menu.index', $token) }}" class="inline-block mt-4 text-sm text-text-secondary hover:text-primary transition-colors" id="back-to-menu-link">
                ← Kembali ke Menu
            </a>
        </div>
    @endif
</div>

<!-- Midtrans Snap Script -->
@if($snapToken)
    <script type="text/javascript" src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result){
                    window.location.href = "{{ route('guest.payment.store', ['token' => $token, 'order' => $order->id]) }}";
                },
                onPending: function(result){
                    window.location.href = "{{ route('guest.payment.store', ['token' => $token, 'order' => $order->id]) }}";
                },
                onError: function(result){
                    alert('Pembayaran gagal. Silakan coba lagi.');
                },
                onClose: function(){
                    // User closed popup without finishing payment
                }
            });
        };
    </script>
@endif
@endsection
