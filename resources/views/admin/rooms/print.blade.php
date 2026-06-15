<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Kamar {{ $room->room_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f3f4f6;
        }
        .print-container {
            background-color: white;
            width: 100%;
            max-width: 600px;
            padding: 40px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-radius: 12px;
        }
        .hotel-name {
            font-size: 28px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .room-title {
            font-size: 22px;
            color: #6b7280;
            margin-bottom: 40px;
        }
        .room-number {
            font-size: 36px;
            color: #c9a84c;
            font-weight: bold;
        }
        .qr-wrapper {
            margin: 0 auto 30px;
            padding: 20px;
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            display: inline-block;
        }
        .instruction {
            font-size: 16px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .footer {
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
        }
        .btn {
            background-color: #1a1a2e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn:hover {
            background-color: #2d3748;
        }
        
        @media print {
            body {
                background-color: white;
                align-items: flex-start;
                padding-top: 50px;
            }
            .print-container {
                box-shadow: none;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn" onclick="window.print()">Cetak QR Code</button>
    </div>

    <div class="print-container">
        <div class="hotel-name">{{ config('hotel.name', 'Hotel Room Service') }}</div>
        <div class="room-title">Room <span class="room-number">{{ $room->room_number }}</span></div>
        
        <div class="qr-wrapper">
            @php
                $url = route('guest.menu.index', $room->qr_token);
            @endphp
            {!! QrCode::size(300)->margin(1)->generate($url) !!}
        </div>
        
        <div class="instruction">
            <strong>Lapar atau Haus?</strong><br>
            Scan QR Code di atas menggunakan kamera HP Anda<br>untuk memesan makanan langsung ke kamar.
        </div>
        
        <div class="footer">
            Token: {{ substr($room->qr_token, 0, 8) }}... &bull; Jangan bagikan QR Code ini ke luar kamar
        </div>
    </div>

    <script>
        // Auto print prompt when opened (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
