<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return Product::query()
            ->whereNull('store_id')
            ->with(['category', 'subcategory', 'department'])
            ->when($this->filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('product_name', 'ilike', "%$search%")
                        ->orWhere('sku', 'ilike', "%$search%")
                        ->orWhere('barcode', 'ilike', "%$search%")
                        ->orWhereHas('category', fn($c) => $c->where('name', 'ilike', "%$search%"))
                        ->orWhereHas('subcategory', fn($s) => $s->where('name', 'ilike', "%$search%"));
                });
            })
            ->when(isset($this->filters['status']) && $this->filters['status'] !== '', function ($q) {
                $q->where('is_active', $this->filters['status']);
            })
            ->latest();
    }

    /**
    * Map each row's data
    * @param mixed $product
    * @return array
    */
    public function map($product): array
    {
        return [
            $product->id,
            $product->product_name,
            $product->sku,
            $product->unit,
            $product->upc,
            $product->plu_code,
            $product->barcode,
            $product->warehouse_markup_percentage,
            $product->cost_price,
            $product->price,
            $product->store_markup_percentage,
            $product->store_retail_price,
            $product->manual_override_price,
            $product->department->name ?? 'N/A',
            $product->category->name ?? 'N/A',
            $product->subcategory->name ?? 'N/A',
            $product->is_active ? 'Active' : 'Inactive',
        ];
    }

    /**
    * Define the Excel Headings
    * @return array
    */
    public function headings(): array
    {
        return [
            'ID',
            'product_name',
            'sku',
            'unit',
            'upc',
            'plu_code',
            'barcode',
            'warehouse_markup_percentage',
            'cost_price',
            'price',
            'store_markup_percentage',
            'store_retail_price',
            'manual_override_price',
            'Department',
            'Category',
            'Subcategory',
            'status'
        ];
    }
}