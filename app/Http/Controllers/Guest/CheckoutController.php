<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index($token)
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('guest.menu.index', $token)
                ->with('error', 'Keranjang Anda kosong.');
        }

        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $taxRate = config('hotel.tax_rate', 11) / 100;
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax;
        
        $estimatedDelivery = config('hotel.estimated_delivery', 30);

        return view('guest.checkout.index', compact('cart', 'subtotal', 'tax', 'total', 'estimatedDelivery', 'token'));
    }

    public function store(Request $request, $token)
    {
        $request->validate([
            'guest_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('guest.menu.index', $token)
                ->with('error', 'Keranjang Anda kosong.');
        }

        $room = $request->room; // Injected by ValidateRoomToken middleware

        try {
            DB::beginTransaction();

            $order = Order::create([
                'room_id' => $room->id,
                'guest_name' => $request->guest_name,
                'notes' => $request->notes,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'estimated_delivery' => now()->addMinutes((int) config('hotel.estimated_delivery', 30)),
            ]);

            foreach ($cart as $item) {
                $order->items()->create([
                    'menu_id' => $item['id'],
                    'menu_name' => $item['name'],
                    'menu_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'notes' => $item['notes'],
                ]);
            }

            $order->calculateTotals();

            DB::commit();

            // Clear the cart after successful order creation
            session()->forget('cart');

            return redirect()->route('guest.payment', ['token' => $token, 'order' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        }
    }
}
