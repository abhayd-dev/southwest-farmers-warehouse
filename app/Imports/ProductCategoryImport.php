<?php

namespace App\Imports;

use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductCategoryImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new ProductCategory([
            'name'      => $row['name'],
            'code'      => $row['code'],
            'is_active' => 1,
        ]);
    }
}