<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index($token)
    {
        $cart = session()->get('cart', []);
        
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $taxRate = config('hotel.tax_rate', 11) / 100;
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax;

        return view('guest.cart.index', compact('cart', 'subtotal', 'tax', 'total', 'token'));
    }

    public function add(Request $request, $token)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $menu = Menu::findOrFail($request->menu_id);
        $cart = session()->get('cart', []);

        // If item exists, increase quantity
        if (isset($cart[$menu->id])) {
            $cart[$menu->id]['quantity'] += $request->quantity;
            if ($request->notes) {
                $cart[$menu->id]['notes'] = $request->notes;
            }
        } else {
            // Add new item to cart
            $cart[$menu->id] = [
                'id' => $menu->id,
                'name' => $menu->name,
                'price' => $menu->price,
                'image' => $menu->image_url,
                'quantity' => $request->quantity,
                'notes' => $request->notes ?? '',
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Menu ditambahkan ke keranjang',
            'cart_count' => collect($cart)->sum('quantity')
        ]);
    }

    public function update(Request $request, $token)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'action' => 'required|in:increase,decrease',
        ]);

        $cart = session()->get('cart', []);
        $menuId = $request->menu_id;

        if (isset($cart[$menuId])) {
            if ($request->action === 'increase') {
                $cart[$menuId]['quantity']++;
            } else {
                $cart[$menuId]['quantity']--;
                if ($cart[$menuId]['quantity'] <= 0) {
                    unset($cart[$menuId]);
                }
            }
            session()->put('cart', $cart);
        }

        return redirect()->route('guest.cart', $token);
    }

    public function remove(Request $request, $token)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
        ]);

        $cart = session()->get('cart', []);
        
        if (isset($cart[$request->menu_id])) {
            unset($cart[$request->menu_id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('guest.cart', $token);
    }
}
