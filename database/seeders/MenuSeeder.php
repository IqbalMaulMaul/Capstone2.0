<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $mainCourse = Category::where('slug', 'main-course')->first();
        $appetizer = Category::where('slug', 'appetizer')->first();
        $dessert = Category::where('slug', 'dessert')->first();
        $beverage = Category::where('slug', 'beverage')->first();

        $menus = [
            // Main Course
            [
                'category_id' => $mainCourse->id,
                'name' => 'Nasi Goreng Spesial',
                'description' => 'Nasi goreng dengan telur, ayam suwir, sosis, dan kerupuk.',
                'price' => 45000,
                'sort_order' => 1,
            ],
            [
                'category_id' => $mainCourse->id,
                'name' => 'Mie Goreng Seafood',
                'description' => 'Mie goreng dengan udang, cumi, dan sayuran segar.',
                'price' => 50000,
                'sort_order' => 2,
            ],
            [
                'category_id' => $mainCourse->id,
                'name' => 'Ayam Bakar Madu',
                'description' => 'Ayam bakar dengan bumbu madu lezat disajikan dengan nasi putih.',
                'price' => 55000,
                'sort_order' => 3,
            ],
            
            // Appetizer
            [
                'category_id' => $appetizer->id,
                'name' => 'French Fries',
                'description' => 'Kentang goreng renyah dengan saus sambal.',
                'price' => 25000,
                'sort_order' => 1,
            ],
            [
                'category_id' => $appetizer->id,
                'name' => 'Chicken Wings',
                'description' => 'Sayap ayam bumbu pedas manis (6 potong).',
                'price' => 35000,
                'sort_order' => 2,
            ],

            // Dessert
            [
                'category_id' => $dessert->id,
                'name' => 'Pancake Ice Cream',
                'description' => 'Pancake lembut dengan topping es krim vanilla dan sirup coklat.',
                'price' => 30000,
                'sort_order' => 1,
            ],
            [
                'category_id' => $dessert->id,
                'name' => 'Pisang Bakar Keju',
                'description' => 'Pisang bakar dengan taburan keju dan susu kental manis.',
                'price' => 25000,
                'sort_order' => 2,
            ],

            // Beverage
            [
                'category_id' => $beverage->id,
                'name' => 'Ice Tea',
                'description' => 'Es teh manis segar.',
                'price' => 15000,
                'sort_order' => 1,
            ],
            [
                'category_id' => $beverage->id,
                'name' => 'Orange Juice',
                'description' => 'Jus jeruk segar murni.',
                'price' => 20000,
                'sort_order' => 2,
            ],
            [
                'category_id' => $beverage->id,
                'name' => 'Kopi Susu Gula Aren',
                'description' => 'Kopi susu dengan gula aren asli.',
                'price' => 25000,
                'sort_order' => 3,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::firstOrCreate(
                ['slug' => Str::slug($menu['name'])],
                $menu
            );
        }
    }
}
