<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('category')->orderBy('category_id')->orderBy('sort_order')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.menus.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'image' => 'nullable|image|max:2048' // max 2MB
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('menus', 'public');
            $validated['image_path'] = $path;
        }

        $validated['is_available'] = $request->boolean('is_available');
        $validated['sort_order'] = Menu::where('category_id', $validated['category_id'])->max('sort_order') + 1;

        Menu::create($validated);

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil ditambahkan!');
    }

    public function edit(Menu $menu)
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.menus.form', compact('menu', 'categories'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'image' => 'nullable|image|max:2048'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            if ($menu->image_path) {
                Storage::disk('public')->delete($menu->image_path);
            }
            $path = $request->file('image')->store('menus', 'public');
            $validated['image_path'] = $path;
        }

        $validated['is_available'] = $request->boolean('is_available');

        $menu->update($validated);

        return redirect()->route('admin.menus.index')->with('success', 'Menu berhasil diperbarui!');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->image_path) {
            Storage::disk('public')->delete($menu->image_path);
        }
        $menu->delete();
        
        return back()->with('success', 'Menu berhasil dihapus!');
    }

    public function toggleAvailability(Menu $menu)
    {
        $menu->update(['is_available' => !$menu->is_available]);
        
        return back()->with('success', 'Status menu ' . $menu->name . ' berhasil diubah!');
    }
}
