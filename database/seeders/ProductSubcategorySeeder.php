<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Support\Str;

class ProductSubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'VEG' => [
                ['name' => 'Potato', 'code' => 'POT'],
                ['name' => 'Tomato', 'code' => 'TOM'],
                ['name' => 'Onion',  'code' => 'ONI'],
            ],
            'FRT' => [
                ['name' => 'Apple',  'code' => 'APP'],
                ['name' => 'Banana', 'code' => 'BAN'],
            ],
            'GRN' => [
                ['name' => 'Rice',  'code' => 'RIC'],
                ['name' => 'Wheat', 'code' => 'WHT'],
            ],
            'DAR' => [
                ['name' => 'Milk',   'code' => 'MLK'],
                ['name' => 'Cheese', 'code' => 'CHS'],
            ],
        ];

        foreach ($data as $categoryCode => $subcategories) {
            $category = ProductCategory::where('code', $categoryCode)->first();

            if (! $category) continue;

            foreach ($subcategories as $sub) {
                ProductSubcategory::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'code'        => $sub['code'],
                    ],
                    [
                        'name'      => $sub['name'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
