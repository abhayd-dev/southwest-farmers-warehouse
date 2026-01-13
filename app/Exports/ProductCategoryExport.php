<?php

namespace App\Exports;

use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductCategoryExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ProductCategory::select('name', 'code', 'is_active')->get();
    }

    public function headings(): array
    {
        return ['Name', 'Code', 'Status'];
    }
}