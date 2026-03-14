<?php

namespace App\Exports\Samples;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductSampleExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'product_name',
            'sku',
            'barcode',
            'upc',
            'plu_code',
            'unit',
            'units_per_carton',
            'cost_price',
            'warehouse_markup_percentage',
            'price',
            'store_markup_percentage',
            'store_retail_price',
            'manual_override_price'
        ];
    }

    public function array(): array
    {
        return [
            [
                'Premium Rice',
                'RIC-001',
                '8901234567890',
                '123456789012',
                '1001',
                'kg',
                '10',
                '50.00',
                '10',
                '55.00',
                '20',
                '66.00',
                ''
            ],
            [
                'Cooking Oil',
                'OIL-002',
                '8901234567891',
                '123456789013',
                '',
                'liter',
                '12',
                '120.00',
                '5',
                '126.00',
                '15',
                '144.90',
                ''
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
