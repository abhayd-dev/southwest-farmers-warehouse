<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductStock;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductImport implements ToCollection, WithHeadingRow
{
    protected $departmentId;
    protected $categoryId;
    protected $subcategoryId;

    public function __construct($categoryId, $subcategoryId, $departmentId)
    {
        $this->categoryId = $categoryId;
        $this->subcategoryId = $subcategoryId;
        $this->departmentId = $departmentId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['product_name'])) continue;

            DB::transaction(function () use ($row) {
                // If special handling for ProductOption is needed as in manual store method
                // We'll follow the logic: Use product_option_id if exists, otherwise create one.
                // For import, we'll create a new option for each product to keep it simple and consistent.

                $option = ProductOption::create([
                    'ware_user_id' => Auth::id(),
                    'store_id' => null, // Warehouse Option
                    'option_name' => $row['product_name'],
                    'sku' => $row['sku'] ?? null,
                    'category_id' => $this->categoryId,
                    'subcategory_id' => $this->subcategoryId,
                    'unit' => $row['unit'],
                    'upc' => (string)($row['upc'] ?? ''),
                    'plu_code' => (string)($row['plu_code'] ?? ''),
                    'barcode' => (string)($row['barcode'] ?? ''),
                    'warehouse_markup_percentage' => (float)($row['warehouse_markup_percentage'] ?? 0),
                    'cost_price' => (float)($row['cost_price'] ?? 0),
                    'base_price' => (float)($row['price'] ?? 0),
                    'mrp' => (float)($row['price'] ?? 0),
                    'is_active' => 1,
                    'department_id' => $this->departmentId
                ]);

                $product = Product::create([
                    'product_option_id' => $option->id,
                    'department_id' => $this->departmentId,
                    'category_id' => $this->categoryId,
                    'subcategory_id' => $this->subcategoryId,
                    'product_name' => $row['product_name'],
                    'sku' => $row['sku'] ?? null,
                    'unit' => $row['unit'],
                    'barcode' => (string)($row['barcode'] ?? ''),
                    'upc' => (string)($row['upc'] ?? ''),
                    'plu_code' => (string)($row['plu_code'] ?? ''),
                    'units_per_carton' => (int)($row['units_per_carton'] ?? 1),
                    'cost_price' => (float)($row['cost_price'] ?? 0),
                    'warehouse_markup_percentage' => (float)($row['warehouse_markup_percentage'] ?? 0),
                    'price' => (float)($row['price'] ?? 0),
                    'store_markup_percentage' => (float)($row['store_markup_percentage'] ?? 0),
                    'store_retail_price' => (float)($row['store_retail_price'] ?? 0),
                    'manual_override_price' => isset($row['manual_override_price']) && $row['manual_override_price'] !== '' ? (float)$row['manual_override_price'] : null,
                    'is_active' => 1,
                    'store_id' => null,
                ]);

                // Initialize Warehouse Stock
                ProductStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => 1,
                    'quantity' => 0
                ]);

                // Generate Barcode Images
                $option->generateBarcodeImage();
                $product->generateBarcodeImage();
            });
        }
    }
}