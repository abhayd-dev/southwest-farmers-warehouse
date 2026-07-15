<?php

namespace App\Exports\Samples;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorSampleExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'name',
            'contact_person',
            'email',
            'phone',
            'address',
            'lead_time_days',
        ];
    }

    public function array(): array
    {
        return [
            ['Fresh Farms Co.', 'John Smith', 'john@freshfarms.com', '+1-555-0101', '123 Farm Road, Springfield', 7],
            ['Green Valley Supplies', 'Jane Doe', 'jane@greenvalley.com', '+1-555-0202', '456 Valley Ave, Riverside', 14],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
