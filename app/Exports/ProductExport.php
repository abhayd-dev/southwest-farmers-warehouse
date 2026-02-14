<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Import this

class ProductExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize // Add interface here
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Eager load relationships to prevent N+1 query performance issues
        return Product::with(['category', 'subcategory', 'department'])->get();
    }

    /**
    * Map each row's data
    * * @param mixed $product
    * @return array
    */
    public function map($product): array
    {
        return [
            $product->id,
            $product->product_name,
            $product->department->name ?? '',
            $product->category->name ?? '',
            $product->subcategory->name ?? '',
            $product->sku,
            $product->barcode,
            $product->unit,
            number_format($product->price, 2),
            $product->cost_price,
            $product->tax_percent,
            $product->is_active ? 'Active' : 'Inactive',
        ];
    }

    /**
    * Define the Excel Headings
    * * @return array
    */
    public function headings(): array
    {
        return [
            'ID',
            'Product Name',
            'Department',
            'Category',
            'Subcategory',
            'SKU',
            'Barcode',
            'Unit',
            'Selling Price',
            'Cost Price',
            'Tax %',
            'Status'
        ];
    }
}