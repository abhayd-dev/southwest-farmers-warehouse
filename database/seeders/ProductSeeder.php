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

            $rows = [
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
            ];

            foreach ($rows as $row) {

                $category = ProductCategory::where('name', $row['category'])->first();
                if (!$category) continue;

                $subcategory = ProductSubcategory::where([
                    'name' => $row['subcategory'],
                    'category_id' => $category->id,
                ])->first();
                if (!$subcategory) continue;
                $price = is_numeric($row['price']) ? $row['price'] : 0;
                $costPrice = is_numeric($row['cost_price']) ? $row['cost_price'] : 0;

                // -------- Product Option --------
                $option = ProductOption::create([
                    'category_id'    => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'option_name'    => $row['name'],
                    'sku'            => $row['sku'],
                    'barcode'        => $row['barcode'],
                    'unit'           => $row['unit'],
                    'tax_percent'    => 0,
                    'cost_price'     => $costPrice,
                    'base_price'     => $price,
                    'mrp'            => $price,
                    'is_active'      => 1,
                ]);


                // -------- Product --------
                Product::create([
                    'product_option_id' => $option->id,
                    'category_id'       => $category->id,
                    'subcategory_id'    => $subcategory->id,
                    'product_name'      => $row['name'],
                    'sku'               => $row['sku'],
                    'barcode'           => $row['barcode'],
                    'unit'              => $row['unit'],
                    'tax_percent'       => 0,
                    'price'             => $price,        // ✅ NEVER NULL
                    'cost_price'        => $costPrice,     // ✅ NEVER NULL
                    'is_active'         => 1,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
