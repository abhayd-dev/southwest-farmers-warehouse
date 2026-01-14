<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Support\Str;

class ProductAndOptionSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // NON FOOD
            ['NON FOOD', 'NON FOOD', 'TUFF DEGREASER (1/Gal)', '1151451', '1 bx', 120.00, 150.00, 'TD001'],
            ['NON FOOD', 'NON FOOD', 'GLASS CLEANER', '1151452', '1 bx', 80.00, 110.00, 'GC002'],

            // HEALTH & BEAUTY
            ['HEALTH & BEAUTY', 'HEALTH & BEAUTY', 'MAXI TONE CREAM 30G', '1149972', '1 bx', 76.80, 120.00, 'MT003'],
            ['HEALTH & BEAUTY', 'HEALTH & BEAUTY', 'MAGIC SHAVING POWDER 5OZ', '1149923', '1 bx', 60.00, 95.00, 'MS004'],
            ['HEALTH & BEAUTY', 'HEALTH & BEAUTY', 'BODY LOTION 200ML', '1149924', '1 pc', 90.00, 140.00, 'BL005'],

            // KITCHEN
            ['KITCHEN', 'KITCHEN', 'DELI - BAKED FISH', '1149532', '1 ea', 200.00, 280.00, 'DF006'],
            ['KITCHEN', 'KITCHEN', 'DELI - SUYA BEEF', '1150296', '4 ct', 300.00, 420.00, 'SB007'],
            ['KITCHEN', 'KITCHEN', 'FRIED CHICKEN', '1150297', '2 ct', 180.00, 260.00, 'FC008'],

            // MEAT
            ['MEAT', 'Beef', 'FRESH BEEF 1KG', '1151001', '1 kg', 450.00, 550.00, 'BF009'],
            ['MEAT', 'Chicken', 'CHICKEN WHOLE', '1151002', '1 kg', 220.00, 280.00, 'CH010'],
            ['MEAT', 'Fish', 'FRESH FISH', '1151003', '1 kg', 300.00, 380.00, 'FS011'],
            ['MEAT', 'Goat', 'GOAT MEAT', '1151004', '1 kg', 650.00, 780.00, 'GT012'],
            ['MEAT', 'Turkey', 'TURKEY MEAT', '1151005', '1 kg', 520.00, 650.00, 'TK013'],

            // GROCERY
            ['Grocery', 'Grocery', 'RICE 5KG', '1152001', '5 kg', 450.00, 520.00, 'RC014'],
            ['Grocery', 'CANNED & DRIED FOOD', 'CANNED BEANS', '1152002', '1 pc', 90.00, 130.00, 'CB015'],
            ['Grocery', 'CANNED & DRIED FOOD', 'DRIED PEAS', '1152003', '1 kg', 110.00, 160.00, 'DP016'],

            // BEVERAGES
            ['BEVERAGES', 'SODA', 'COLA 1L', '1153001', '1 ltr', 40.00, 65.00, 'CL017'],
            ['BEVERAGES', 'BEVERAGES', 'ORANGE JUICE', '1153002', '1 ltr', 70.00, 110.00, 'OJ018'],

            // PRODUCE
            ['PRODUCE', 'PRODUCE', 'FRESH TOMATO', '1154001', '1 kg', 30.00, 50.00, 'TM019'],
            ['PRODUCE', 'PRODUCE', 'ONION', '1154002', '1 kg', 28.00, 45.00, 'ON020'],
            ['PRODUCE', 'PRODUCE', 'POTATO', '1154003', '1 kg', 25.00, 40.00, 'PT021'],

            // HOUSEHOLD
            ['HOUSEHOLD', 'CLEANING GOODS', 'FLOOR CLEANER', '1155001', '1 ltr', 85.00, 130.00, 'FC022'],
            ['HOUSEHOLD', 'CLEANING GOODS', 'TOILET CLEANER', '1155002', '500 ml', 60.00, 95.00, 'TC023'],

            // DISPOSABLE
            ['DISPOSABLE ITEMS', 'DISPOSABLE ITEMS', 'PAPER PLATES', '1156001', '50 ct', 70.00, 120.00, 'PP024'],
            ['DISPOSABLE ITEMS', 'DISPOSABLE ITEMS', 'PLASTIC CUPS', '1156002', '100 ct', 90.00, 150.00, 'PC025'],

            // FROZEN
            ['FROZEN', 'FROZEN', 'FROZEN PEAS', '1157001', '1 kg', 130.00, 180.00, 'FP026'],
            ['FROZEN', 'FROZEN', 'FROZEN FRIES', '1157002', '1 kg', 150.00, 210.00, 'FF027'],

            // TEST
            ['Test Dept', 'Test Sub Dept', 'TEST PRODUCT A', '9990001', '1 pc', 10.00, 20.00, 'TS028'],
            ['Test Dept', 'Test Sub Dept', 'TEST PRODUCT B', '9990002', '1 pc', 12.00, 25.00, 'TS029'],
            ['Test Dept', 'Test Sub Dept', 'TEST PRODUCT C', '9990003', '1 pc', 15.00, 30.00, 'TS030'],
        ];

        foreach ($products as $row) {
            [$catName, $subName, $name, $sku, $unit, $cost, $price, $barcode] = $row;

            $category = ProductCategory::where('name', $catName)->first();
            $subcategory = ProductSubcategory::where('name', $subName)
                ->where('category_id', $category->id)
                ->first();

            if (!$category || !$subcategory) {
                continue;
            }

            // -------- Product Option --------
            $option = ProductOption::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'option_name' => $name,
                    'barcode' => $barcode,
                    'unit' => $unit,
                    'cost_price' => $cost,
                    'base_price' => $price,
                    'mrp' => $price,
                    'tax_percent' => 0,
                    'is_active' => 1,
                ]
            );

            // -------- Product --------
            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'product_option_id' => $option->id,
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'product_name' => $name,
                    'barcode' => $barcode,
                    'unit' => $unit,
                    'cost_price' => $cost,
                    'price' => $price,
                    'tax_percent' => 0,
                    'is_active' => 1,
                ]
            );
        }
    }
}
