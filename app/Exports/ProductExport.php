<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::select(
            'product_name',
            'sku',
            'unit',
            'tax_percent',
            'cost_price',
            'price',
            'barcode'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Unit',
            'Tax %',
            'Cost Price',
            'Price',
            'Barcode'
        ];
    }
}