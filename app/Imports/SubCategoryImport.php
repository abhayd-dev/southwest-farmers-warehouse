<?php

namespace App\Imports;

use App\Models\ProductSubcategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SubCategoryImport implements ToModel, WithHeadingRow
{
    protected $categoryId;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function model(array $row)
    {
        if (empty($row['name'])) return null;

        return new ProductSubcategory([
            'category_id' => $this->categoryId,
            'name'        => $row['name'],
            'code'        => $row['code'],
            'is_active'   => 1,
            'store_id'    => null,
        ]);
    }
}
