<?php

namespace App\Imports;

use App\Models\ProductOption;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductOptionImport implements ToModel, WithHeadingRow
{
    protected $categoryId;
    protected $subcategoryId;

    public function __construct($categoryId, $subcategoryId = null)
    {
        $this->categoryId    = $categoryId;
        $this->subcategoryId = $subcategoryId;
    }

    public function model(array $row)
    {
        return new ProductOption([
            'category_id'    => $this->categoryId,
            'subcategory_id' => $this->subcategoryId,
            'option_name'    => $row['option_name'],
            'sku'            => $row['sku'],
            'unit'           => $row['unit'],
            'tax_percent'    => $row['tax_percent'],
            'cost_price'     => $row['cost_price'],
            'base_price'     => $row['base_price'],
            'mrp'            => $row['mrp'],
            'is_active'      => true,
        ]);
    }
}

