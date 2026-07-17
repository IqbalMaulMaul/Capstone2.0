<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ─── Dashboard ───────────────────────────────────────

    public function dashboard()
    {
        $todayOrders = Order::today()->count();
        $pendingOrders = Order::byStatus(Order::STATUS_PAID)->count();
        $kitchenActive = Order::kitchenActive()->count();
        
        $todayRevenue = Payment::whereDate('paid_at', today())
                            ->where('status', Payment::STATUS_SUCCESS)
                            ->sum('amount');

        $recentOrders = Order::with('room')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'room_number' => $order->room->room_number ?? '-',
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'created_at' => $order->created_at->toISOString(),
                    'created_at_human' => $order->created_at->diffForHumans(),
                    'total' => (float)$order->total,
                    'formatted_total' => $order->formatted_total,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'todayOrders' => $todayOrders,
                'pendingOrders' => $pendingOrders,
                'kitchenActive' => $kitchenActive,
                'todayRevenue' => (float)$todayRevenue,
                'recentOrders' => $recentOrders,
            ]
        ]);
    }

    // ─── Categories CRUD ─────────────────────────────────

    public function categories()
    {
        $categories = Category::withCount('menus')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Category::max('sort_order') + 1;
        }

        $category = Category::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category->loadCount('menus'),
        ], 201);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil diperbarui',
            'data' => $category->loadCount('menus'),
        ]);
    }

    public function destroyCategory(Category $category)
    {
        if ($category->menus()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat menghapus kategori karena masih ada menu di dalamnya.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil dihapus',
        ]);
    }

    // ─── Menus CRUD ──────────────────────────────────────

    public function menus()
    {
        $menus = Menu::with('category')->get()->map(function ($menu) {
            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'description' => $menu->description,
                'category_id' => $menu->category_id,
                'category_name' => $menu->category->name ?? '-',
                'price' => (float)$menu->price,
                'formatted_price' => 'Rp' . number_format($menu->price, 0, ',', '.'),
                'is_available' => $menu->is_available,
                'image_url' => $menu->image_path ? (Str::startsWith($menu->image_path, 'http') ? $menu->image_path : asset('storage/' . $menu->image_path)) : null,
                'sort_order' => $menu->sort_order,
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_available'] = $request->boolean('is_available', true);
        $validated['sort_order'] = Menu::where('category_id', $validated['category_id'])->max('sort_order') + 1;

        if ($request->has('image_url')) {
            $validated['image_path'] = $request->image_url;
            unset($validated['image_url']);
        } elseif ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $response = \Illuminate\Support\Facades\Http::attach(
                'image', file_get_contents($imageFile->getRealPath()), $imageFile->getClientOriginalName()
            )->post('https://api.imgbb.com/1/upload', [
                'key' => env('IMGBB_API_KEY', '70b73974abbd9988225b9e79b0460bfc')
            ]);
            
            if ($response->successful() && isset($response->json()['data']['url'])) {
                $validated['image_path'] = $response->json()['data']['url'];
            } else {
                $path = $imageFile->store('menus', 'public');
                $validated['image_path'] = $path;
            }
        }

        $menu = Menu::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu berhasil ditambahkan',
            'data' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'description' => $menu->description,
                'category_id' => $menu->category_id,
                'category_name' => $menu->category->name ?? '-',
                'price' => (float)$menu->price,
                'formatted_price' => 'Rp' . number_format($menu->price, 0, ',', '.'),
                'is_available' => $menu->is_available,
                'image_url' => $menu->image_path ? (Str::startsWith($menu->image_path, 'http') ? $menu->image_path : asset('storage/' . $menu->image_path)) : null,
            ],
        ], 201);
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_available'] = $request->boolean('is_available', true);

        if ($request->has('image_url')) {
            if ($menu->image_path && !Str::startsWith($menu->image_path, 'http')) {
                Storage::disk('public')->delete($menu->image_path);
            }
            $validated['image_path'] = $request->image_url;
            unset($validated['image_url']);
        } elseif ($request->hasFile('image')) {
            if ($menu->image_path && !Str::startsWith($menu->image_path, 'http')) {
                Storage::disk('public')->delete($menu->image_path);
            }
            
            $imageFile = $request->file('image');
            $response = \Illuminate\Support\Facades\Http::attach(
                'image', file_get_contents($imageFile->getRealPath()), $imageFile->getClientOriginalName()
            )->post('https://api.imgbb.com/1/upload', [
                'key' => env('IMGBB_API_KEY', '70b73974abbd9988225b9e79b0460bfc')
            ]);
            
            if ($response->successful() && isset($response->json()['data']['url'])) {
                $validated['image_path'] = $response->json()['data']['url'];
            } else {
                $path = $imageFile->store('menus', 'public');
                $validated['image_path'] = $path;
            }
        }

        $menu->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Menu berhasil diperbarui',
            'data' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'description' => $menu->description,
                'category_id' => $menu->category_id,
                'category_name' => $menu->category->name ?? '-',
                'price' => (float)$menu->price,
                'formatted_price' => 'Rp' . number_format($menu->price, 0, ',', '.'),
                'is_available' => $menu->is_available,
                'image_url' => $menu->image_path ? (Str::startsWith($menu->image_path, 'http') ? $menu->image_path : asset('storage/' . $menu->image_path)) : null,
            ],
        ]);
    }

    public function destroyMenu(Menu $menu)
    {
        if ($menu->image_path && !Str::startsWith($menu->image_path, 'http')) {
            Storage::disk('public')->delete($menu->image_path);
        }
        $menu->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Menu berhasil dihapus',
        ]);
    }

    public function toggleMenu(Menu $menu)
    {
        $menu->update(['is_available' => !$menu->is_available]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status menu berhasil diubah',
            'data' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'is_available' => $menu->is_available,
            ],
        ]);
    }

    // ─── Rooms CRUD ──────────────────────────────────────

    public function rooms()
    {
        $rooms = Room::all()->map(function ($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'is_active' => $room->is_active,
                'qr_token' => $room->qr_token,
                'qr_url' => route('guest.menu.index', ['token' => $room->qr_token]),
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $rooms
        ]);
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number',
            'floor' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['qr_token'] = Str::uuid()->toString();

        $room = Room::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Kamar berhasil ditambahkan',
            'data' => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'is_active' => $room->is_active,
                'qr_token' => $room->qr_token,
                'qr_url' => route('guest.menu.index', ['token' => $room->qr_token]),
            ],
        ], 201);
    }

    public function updateRoom(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:50|unique:rooms,room_number,' . $room->id,
            'floor' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'regenerate_token' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->boolean('regenerate_token')) {
            $validated['qr_token'] = Str::uuid()->toString();
        }

        unset($validated['regenerate_token']);
        $room->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Kamar berhasil diperbarui',
            'data' => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'is_active' => $room->is_active,
                'qr_token' => $room->qr_token,
                'qr_url' => route('guest.menu.index', ['token' => $room->qr_token]),
            ],
        ]);
    }

    public function destroyRoom(Room $room)
    {
        $room->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kamar berhasil dihapus',
        ]);
    }

    // ─── Orders (Read-only) ─────────────────────────────

    public function orders(Request $request)
    {
        $query = Order::with(['room', 'payment'])->orderBy('created_at', 'desc');

        // Optional status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20);

        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'room_number' => $order->room->room_number ?? '-',
                'guest_name' => $order->guest_name,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'total' => (float)$order->total,
                'formatted_total' => $order->formatted_total,
                'payment_status' => $order->payment->status ?? null,
                'payment_method' => $order->payment->method ?? null,
                'created_at' => $order->created_at->toISOString(),
                'created_at_human' => $order->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }

    public function orderShow(Order $order)
    {
        $order->load(['room', 'items.menu', 'payment']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'room_number' => $order->room->room_number ?? '-',
                'guest_name' => $order->guest_name,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'notes' => $order->notes,
                'subtotal' => (float)$order->subtotal,
                'tax' => (float)$order->tax,
                'total' => (float)$order->total,
                'formatted_total' => $order->formatted_total,
                'estimated_delivery' => $order->estimated_delivery?->toISOString(),
                'created_at' => $order->created_at->toISOString(),
                'created_at_human' => $order->created_at->diffForHumans(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'menu_name' => $item->menu_name,
                        'menu_price' => (float)$item->menu_price,
                        'quantity' => $item->quantity,
                        'subtotal' => (float)$item->subtotal,
                        'notes' => $item->notes,
                        'image_url' => $item->menu && $item->menu->image_path
                            ? (Str::startsWith($item->menu->image_path, 'http') ? $item->menu->image_path : asset('storage/' . $item->menu->image_path)) : null,
                    ];
                }),
                'payment' => $order->payment ? [
                    'id' => $order->payment->id,
                    'transaction_id' => $order->payment->transaction_id,
                    'amount' => (float)$order->payment->amount,
                    'formatted_amount' => 'Rp' . number_format($order->payment->amount, 0, ',', '.'),
                    'status' => $order->payment->status,
                    'method' => $order->payment->method,
                    'payment_type' => $order->payment->payment_type,
                    'paid_at' => $order->payment->paid_at?->toISOString(),
                    'paid_at_human' => $order->payment->paid_at?->diffForHumans(),
                ] : null,
            ],
        ]);
    }

    // ─── Payments (Read-only) ────────────────────────────

    public function payments(Request $request)
    {
        $query = Payment::with(['order.room'])->orderBy('created_at', 'desc');

        // Optional status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(20);

        $payments->getCollection()->transform(function ($p) {
            return [
                'id' => $p->id,
                'order_id' => $p->order_id,
                'order_number' => $p->order->order_number ?? '-',
                'room_number' => $p->order->room->room_number ?? '-',
                'transaction_id' => $p->transaction_id,
                'amount' => (float)$p->amount,
                'formatted_amount' => 'Rp' . number_format($p->amount, 0, ',', '.'),
                'status' => $p->status,
                'method' => $p->method,
                'payment_type' => $p->payment_type,
                'paid_at' => $p->paid_at?->toISOString(),
                'paid_at_human' => $p->paid_at?->diffForHumans(),
                'created_at' => $p->created_at->toISOString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ]);
    }
}
