<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Vegetables', 'code' => 'VEG'],
            ['name' => 'Fruits',     'code' => 'FRT'],
            ['name' => 'Grains',     'code' => 'GRN'],
            ['name' => 'Dairy',      'code' => 'DAR'],
        ];

        foreach ($categories as $category) {
            ProductCategory::updateOrCreate(
                ['code' => $category['code']], // unique key
                [
                    'name'      => $category['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
