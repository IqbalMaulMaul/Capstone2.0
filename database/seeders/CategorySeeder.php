<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Main Course',
                'icon' => 'utensils',
                'sort_order' => 1,
            ],
            [
                'name' => 'Appetizer',
                'icon' => 'pizza-slice',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dessert',
                'icon' => 'ice-cream',
                'sort_order' => 3,
            ],
            [
                'name' => 'Beverage',
                'icon' => 'coffee',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category
            );
        }
    }
}
