<?php

namespace App\Imports;

use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoryImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['name'])) return null;

        return new ProductCategory([
            'name'      => $row['name'],
            'code'      => $row['code'],
            'is_active' => 1,
            'store_id'  => null,
        ]);
    }
}
