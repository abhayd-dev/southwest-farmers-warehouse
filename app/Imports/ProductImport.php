<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductStock;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductImport implements ToCollection
{
    protected $departmentId;
    protected $categoryId;
    protected $subcategoryId;

    public function __construct($departmentId, $categoryId, $subcategoryId)
    {
        $this->departmentId = $departmentId;
        $this->categoryId = $categoryId;
        $this->subcategoryId = $subcategoryId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows->skip(1) as $row) {
            if (!isset($row[0])) continue; 

            DB::transaction(function () use ($row) {
                
                $option = ProductOption::firstOrCreate(
                    ['sku' => $row[1]], 
                    [
                        'option_name' => $row[0],
                        'category_id' => $this->categoryId,
                        'subcategory_id' => $this->subcategoryId,
                        'unit'        => $row[3],
                        'base_price'  => $row[6],
                        'cost_price'  => $row[5],
                        'tax_percent' => $row[4],
                        'mrp'         => $row[6],
                        'barcode'     => $row[9] ?? null,
                        'is_active'   => 1,
                        'department_id' => $this->departmentId ?? null
                    ]
                );

                $product = Product::create([
                    'product_option_id' => $option->id,
                    'category_id'       => $this->categoryId,
                    'subcategory_id'    => $this->subcategoryId,
                    'product_name'      => $row[0],
                    'sku'               => $row[1],
                    'unit'              => $row[3],
                    'price'             => $row[6],
                    'cost_price'        => $row[5],
                    'tax_percent'       => $row[4],
                    'barcode'           => $row[9] ?? null,
                    'department_id'     => $this->departmentId ?? null,
                    'is_active'         => 1
                ]);

                ProductStock::firstOrCreate([
                    'product_id'   => $product->id,
                    'warehouse_id' => 1
                ]);
            });
        }
    }
}