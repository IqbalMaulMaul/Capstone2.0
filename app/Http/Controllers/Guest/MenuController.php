<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request, $token)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category');

        $categories = Category::active()->ordered()->get();
        
        $menusQuery = Menu::available()->ordered();

        if ($search) {
            $menusQuery->search($search);
        }

        if ($categoryId) {
            $menusQuery->where('category_id', $categoryId);
        }

        // Group by category if no specific category or search is applied
        if (!$search && !$categoryId) {
            $menus = $menusQuery->get()->groupBy('category.name');
        } else {
            $menus = ['Hasil Pencarian' => $menusQuery->get()];
        }

        return view('guest.menu.index', compact('categories', 'menus', 'search', 'categoryId', 'token'));
    }

    public function show($token, $slug)
    {
        $menu = Menu::available()->where('slug', $slug)->firstOrFail();
        
        return view('guest.menu.show', compact('menu', 'token'));
    }
}
