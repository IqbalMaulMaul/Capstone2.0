<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Snap Token for an Order
     */
    public function createSnapToken(Order $order): ?string
    {
        $itemDetails = [];
        
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id'       => $item->menu_id,
                'price'    => (int) $item->menu_price,
                'quantity' => $item->quantity,
                'name'     => substr($item->menu_name, 0, 50), // Midtrans max length is 50
            ];
        }

        // Add Tax as an item
        if ($order->tax > 0) {
            $itemDetails[] = [
                'id'       => 'TAX',
                'price'    => (int) $order->tax,
                'quantity' => 1,
                'name'     => 'Tax (' . config('hotel.tax_rate') . '%)',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->guest_name ?: 'Guest',
                'last_name'  => 'Room ' . $order->room->room_number,
                'email'      => 'guest' . $order->room->room_number . '@hotel.com',
            ],
            'item_details' => $itemDetails,
            'custom_field1' => $order->room->room_number,
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit'       => 'minutes',
                'duration'   => config('hotel.payment_expiry', 30),
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check Transaction Status manually
     */
    public function getTransactionStatus(string $orderId)
    {
        try {
            return Transaction::status($orderId);
        } catch (\Exception $e) {
            Log::error('Midtrans Status Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cancel a Transaction
     */
    public function cancelTransaction(string $orderId)
    {
        try {
            return Transaction::cancel($orderId);
        } catch (\Exception $e) {
            Log::error('Midtrans Cancel Error: ' . $e->getMessage());
            return false;
        }
    }
}
