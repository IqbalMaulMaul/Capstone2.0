<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            color: #000;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        .receipt {
            max-width: 300px; /* Thermal printer size approx */
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .border-top { border-top: 1px dashed #000; padding-top: 8px; mt-2; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 8px; mb-2; }
        .flex { display: flex; justify-content: space-between; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup</button>
    </div>

    <div class="receipt">
        <div class="text-center mb-4 border-bottom">
            <h2 class="font-bold" style="margin: 0 0 5px 0;">{{ config('app.name', 'Hotel Room Service') }}</h2>
            <p style="margin: 0;">Layanan Kamar</p>
        </div>

        <div class="mb-4">
            <div class="flex"><span>Order ID:</span> <span>#{{ $order->order_number }}</span></div>
            <div class="flex"><span>Tanggal:</span> <span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="flex"><span>Kamar:</span> <span class="font-bold">{{ $order->room->room_number }}</span></div>
            <div class="flex"><span>Tamu:</span> <span>{{ $order->guest_name ?? '-' }}</span></div>
        </div>

        <div class="border-top border-bottom">
            <div class="font-bold mb-2">Item Pesanan:</div>
            @foreach($order->items as $item)
            <div style="margin-bottom: 8px;">
                <div>{{ $item->menu_name }}</div>
                <div class="flex text-right" style="padding-left: 10px;">
                    <span>{{ $item->quantity }} x {{ number_format($item->menu_price, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($item->notes)
                <div style="font-size: 12px; font-style: italic; padding-left: 10px;">Catatan: {{ $item->notes }}</div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="border-bottom">
            <div class="flex">
                <span>Subtotal:</span>
                <span>{{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex">
                <span>Pajak:</span>
                <span>{{ number_format($order->tax, 0, ',', '.') }}</span>
            </div>
            <div class="flex font-bold" style="margin-top: 5px; font-size: 16px;">
                <span>Total:</span>
                <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="mb-4">
            <div class="flex">
                <span>Status Order:</span>
                <span>{{ strtoupper($order->status) }}</span>
            </div>
            <div class="flex">
                <span>Pembayaran:</span>
                <span>{{ $order->payment ? strtoupper($order->payment->status) : 'UNPAID' }}</span>
            </div>
        </div>

        <div class="text-center" style="margin-top: 20px; font-size: 12px;">
            <p>Terima Kasih</p>
            <p>Selamat Menikmati Hidangan Anda</p>
        </div>
    </div>
</body>
</html>
