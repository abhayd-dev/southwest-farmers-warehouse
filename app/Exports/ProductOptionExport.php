<?php

namespace App\Exports;

use App\Models\ProductOption;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductOptionExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ProductOption::select(
            'option_name','sku','unit','tax_percent',
            'cost_price','base_price','mrp','is_active'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Option Name',
            'SKU',
            'Unit',
            'Tax %',
            'Cost Price',
            'Base Price',
            'MRP',
            'Status'
        ];
    }
}
