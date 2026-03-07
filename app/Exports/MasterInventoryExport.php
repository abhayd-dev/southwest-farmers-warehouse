<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MasterInventoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'SKU/UPC',
            'Item Description',
            'Category',
            'Location',
            'Qty on Hand',
            'Landed Cost',
            'Total Valuation',
            'Retail Price',
            'Margin %'
        ];
    }

    public function map($product): array
    {
        $landedCost = $product->cost_price ?? 0;
        $qtyOnHand = $product->getWarehouseQuantity();
        $totalValuation = $qtyOnHand * $landedCost;
        $margin = $product->retail_price > 0 ? (($product->retail_price - $landedCost) / $product->retail_price) * 100 : 0;

        return [
            $product->upc ?? $product->sku,
            $product->product_name,
            $product->category->name ?? 'N/A',
            'Warehouse',
            $qtyOnHand,
            '$' . number_format($landedCost, 2),
            '$' . number_format($totalValuation, 2),
            '$' . number_format($product->retail_price ?? 0, 2),
            number_format($margin, 2) . '%'
        ];
    }
}
