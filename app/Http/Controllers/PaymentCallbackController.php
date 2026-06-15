<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('Midtrans Notification received', $payload);

        // Security Check: Verify signature key
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $serverKey = config('midtrans.server_key');
        $signatureKey = $payload['signature_key'] ?? null;

        $calculatedSignature = hash("sha512", $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $calculatedSignature) {
            Log::error('Midtrans Invalid Signature', ['calculated' => $calculatedSignature, 'received' => $signatureKey]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $payload['transaction_status'];
        $paymentType = $payload['payment_type'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        $order = Order::where('order_number', $orderId)->first();

        if (!$order) {
            Log::error("Midtrans Order Not Found: {$orderId}");
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = $order->payment;
        if (!$payment) {
            Log::error("Midtrans Payment Record Not Found for Order: {$orderId}");
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Log the event
        PaymentLog::create([
            'payment_id' => $payment->id,
            'event' => $transactionStatus,
            'payload' => $payload,
        ]);

        // Map Midtrans payment type to our enum
        $methodMapping = [
            'qris' => 'qris',
            'gopay' => 'e_wallet',
            'shopeepay' => 'e_wallet',
            'bank_transfer' => 'bank_transfer',
            'echannel' => 'bank_transfer',
            'bca_klikpay' => 'bank_transfer',
            'cimb_clicks' => 'bank_transfer',
        ];

        if ($paymentType && isset($methodMapping[$paymentType])) {
            $payment->method = $methodMapping[$paymentType];
        }

        // Handle Status
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $payment->status = Payment::STATUS_PENDING;
            } else if ($fraudStatus == 'accept') {
                $this->markAsSuccess($order, $payment, $payload);
            }
        } else if ($transactionStatus == 'settlement') {
            $this->markAsSuccess($order, $payment, $payload);
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $payment->status = Payment::STATUS_FAILED;
            $payment->save();
            
            if ($order->isPending()) {
                $order->update(['status' => Order::STATUS_CANCELLED]);
            }
        } else if ($transactionStatus == 'pending') {
            $payment->status = Payment::STATUS_PENDING;
            $payment->save();
        } else if ($transactionStatus == 'refund') {
            $payment->status = Payment::STATUS_REFUNDED;
            $payment->save();
            $order->update(['status' => Order::STATUS_CANCELLED]);
        }

        return response()->json(['message' => 'OK']);
    }

    protected function markAsSuccess(Order $order, Payment $payment, array $payload)
    {
        // Prevent double processing
        if ($payment->status === Payment::STATUS_SUCCESS) {
            return;
        }

        $payment->status = Payment::STATUS_SUCCESS;
        $payment->paid_at = now();
        $payment->transaction_id = $payload['transaction_id'] ?? null;
        $payment->midtrans_response = $payload;
        $payment->save();

        if ($order->isPending()) {
            $order->status = Order::STATUS_PAID;
            $order->save();

            // Broadcast OrderStatusUpdated to Guest
            \App\Events\OrderStatusUpdated::dispatch($order);

            // TODO: Broadcast NewOrderEvent to Kitchen once it is built
            // \App\Events\NewOrderEvent::dispatch($order);
        }
    }
}
