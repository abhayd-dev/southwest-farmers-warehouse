<?php

namespace App\Exports;

use App\Models\ProductSubcategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductSubcategoryExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ProductSubcategory::with('category')->get();
    }

    public function map($row): array
    {
        return [
            $row->category->name ?? 'N/A',
            $row->name,
            $row->code,
            $row->is_active ? 'Active' : 'Inactive'
        ];
    }

    public function headings(): array
    {
        return ['Category', 'Subcategory Name', 'Code', 'Status'];
    }
}