<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Support\Str;

class ProductCategoryAndSubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'BEVERAGES' => ['BEVERAGES', 'SODA'],
            'Cosmetics' => ['Cosmetics'],
            'DISPOSABLE ITEMS' => ['DISPOSABLE ITEMS'],
            'FROZEN' => ['FROZEN'],
            'Grocery' => ['Grocery', 'CANNED & DRIED FOOD'],
            'HEALTH & BEAUTY' => ['HEALTH & BEAUTY'],
            'HOUSEHOLD' => ['CLEANING GOODS'],
            'KITCHEN' => ['KITCHEN'],
            'MEAT' => ['Beef', 'Chicken', 'Fish', 'Goat', 'Turkey'],
            'NON FOOD' => ['NON FOOD'],
            'Non-Grocery' => ['Non-Grocery'],
            'PRODUCE' => ['PRODUCE'],
            'Test Dept' => ['Test Sub Dept'],
        ];

        foreach ($data as $categoryName => $subcategories) {

            // -------- Category --------
            $category = ProductCategory::updateOrCreate(
                ['code' => Str::upper(Str::slug($categoryName, '_'))],
                [
                    'name' => $categoryName,
                    'is_active' => 1,
                ]
            );

            // -------- Subcategories --------
            foreach ($subcategories as $subName) {
                ProductSubcategory::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'code' => Str::upper(Str::slug($subName, '_')),
                    ],
                    [
                        'name' => $subName,
                        'is_active' => 1,
                    ]
                );
            }
        }
    }
}
