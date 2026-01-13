<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductCategory;
use App\Models\ProductSubcategory;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {

            // Products List (Combined from your previous code + extracted file data)
            $rows = [
                // --- Already Seeded (from your code) ---
                [
                    'category' => 'NON FOOD',
                    'subcategory' => 'NON FOOD',
                    'name' => 'TUFF DEGREASER (1/Gal)',
                    'sku' => '1151451',
                    'unit' => '1 bx',
                    'cost_price' => null,
                    'price' => null,
                    'barcode' => 'TD',
                ],
                [
                    'category' => 'HEALTH & BEAUTY',
                    'subcategory' => 'HEALTH & BEAUTY',
                    'name' => 'MAXI TONE CREAM 30G 2495',
                    'sku' => '1149972',
                    'unit' => '1 bx',
                    'cost_price' => null,
                    'price' => null,
                    'barcode' => '007',
                ],
                [
                    'category' => 'KITCHEN',
                    'subcategory' => 'KITCHEN',
                    'name' => 'DELI - BAKED FISH (EA)',
                    'sku' => '1149532',
                    'unit' => '1 bx',
                    'cost_price' => null,
                    'price' => null,
                    'barcode' => '103',
                ],
                [
                    'category' => 'KITCHEN',
                    'subcategory' => 'KITCHEN',
                    'name' => 'DELI - SUYA BEEF (4/EA)',
                    'sku' => '1150296',
                    'unit' => '4 ct',
                    'cost_price' => null,
                    'price' => null,
                    'barcode' => '119',
                ],
                [
                    'category' => 'HEALTH & BEAUTY',
                    'subcategory' => 'HEALTH & BEAUTY',
                    'name' => 'MAGIC SHAVING POWDER (5OZ) 164',
                    'sku' => '1149923',
                    'unit' => '1 bx',
                    'cost_price' => 76.80,
                    'price' => null,
                    'barcode' => '00028000002954',
                ],

                // --- NEW PRODUCTS (Extracted from Files) ---
                [
                    'category' => 'KITCHEN',
                    'subcategory' => 'KITCHEN',
                    'name' => 'MOI MOI WITH LEAF',
                    'sku' => '1151412',
                    'unit' => '1 ea',
                    'cost_price' => null,
                    'price' => null,
                    'barcode' => '215',
                ],
                [
                    'category' => 'KITCHEN',
                    'subcategory' => 'KITCHEN',
                    'name' => 'YAM PORRIDGE SMALL PAN',
                    'sku' => '1150893',
                    'unit' => 'bx',
                    'cost_price' => null,
                    'price' => null,
                    'barcode' => '223',
                ],
                [
                    'category' => 'KITCHEN',
                    'subcategory' => 'KITCHEN',
                    'name' => 'YAM PORRIDGE LARGE PAN',
                    'sku' => '1150894',
                    'unit' => '1 ea',
                    'cost_price' => null,
                    'price' => null,
                    'barcode' => '224',
                ],
                [
                    'category' => 'BEVERAGES',
                    'subcategory' => 'BEVERAGES',
                    'name' => 'NESTLE MILO ACTIVE GO DRINK 240ML(8OZ)',
                    'sku' => '1148824',
                    'unit' => '24',
                    'cost_price' => 1.725,
                    'price' => null,
                    'barcode' => '00028000239473',
                ],
                [
                    'category' => 'Grocery',
                    'subcategory' => 'CANNED & DRIED FOOD',
                    'name' => 'SYSCO - HUNTS TOMATO PASTE 24/12#',
                    'sku' => '1151838',
                    'unit' => '1',
                    'cost_price' => 51.60,
                    'price' => null,
                    'barcode' => '94551336005',
                ],
                [
                    'category' => 'Grocery',
                    'subcategory' => 'CANNED & DRIED FOOD',
                    'name' => 'SYSCO - HUNTS TOMATO PASTE 6/10#',
                    'sku' => '1151837',
                    'unit' => '1',
                    'cost_price' => 64.27,
                    'price' => null,
                    'barcode' => '94551337009',
                ],
                [
                    'category' => 'PRODUCE',
                    'subcategory' => 'PRODUCE',
                    'name' => 'SPICY WORLD SEMOLINA 12X4LB',
                    'sku' => '1151150',
                    'unit' => '12',
                    'cost_price' => 4.42,
                    'price' => null,
                    'barcode' => '990000002992',
                ],
                [
                    'category' => 'BEVERAGES',
                    'subcategory' => 'SODA',
                    'name' => 'AFRIKAN DELISH - SPICY SUYA MIX',
                    'sku' => '1151699',
                    'unit' => '1',
                    'cost_price' => 132.00,
                    'price' => null,
                    'barcode' => '9373626342146',
                ],
            ];

            foreach ($rows as $row) {

                // Find Category
                $category = ProductCategory::where('name', $row['category'])->first();
                if (!$category) {
                    // Fallback: If category logic is strict, skip. If lenient, could create here.
                    continue; 
                }

                // Find Subcategory
                $subcategory = ProductSubcategory::where([
                    'name' => $row['subcategory'],
                    'category_id' => $category->id,
                ])->first();
                if (!$subcategory) continue;

                $price = is_numeric($row['price']) ? $row['price'] : 0;
                $costPrice = is_numeric($row['cost_price']) ? $row['cost_price'] : 0;

                // -------- Product Option --------
                // Using updateOrCreate to prevent duplicates if seeded multiple times
                $option = ProductOption::updateOrCreate(
                    ['sku' => $row['sku']], // Unique Identifier
                    [
                        'category_id'    => $category->id,
                        'subcategory_id' => $subcategory->id,
                        'option_name'    => $row['name'],
                        'barcode'        => $row['barcode'],
                        'unit'           => $row['unit'],
                        'tax_percent'    => 0,
                        'cost_price'     => $costPrice,
                        'base_price'     => $price,
                        'mrp'            => $price,
                        'is_active'      => 1,
                    ]
                );

                // -------- Product --------
                Product::updateOrCreate(
                    ['product_option_id' => $option->id],
                    [
                        'category_id'       => $category->id,
                        'subcategory_id'    => $subcategory->id,
                        'product_name'      => $row['name'],
                        'sku'               => $row['sku'],
                        'barcode'           => $row['barcode'],
                        'unit'              => $row['unit'],
                        'tax_percent'       => 0,
                        'price'             => $price,
                        'cost_price'        => $costPrice,
                        'is_active'         => 1,
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}