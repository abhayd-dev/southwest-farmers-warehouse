<?php

namespace App\Imports;

use App\Models\ProductCategory;
use App\Services\NotificationService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CategoryImport implements ToCollection, WithHeadingRow, ShouldQueue, WithChunkReading, WithBatchInserts, WithEvents
{
    protected $authUserId;

    public function __construct($authUserId = null)
    {
        $this->authUserId = $authUserId ?? Auth::id();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['name'])) continue;

            ProductCategory::create([
                'name'      => $row['name'],
                'code'      => $row['code'] ?? null,
                'is_active' => 1,
                'store_id'  => null,
            ]);
        }
    }

    public function batchSize(): int
    {
        return 20;
    }

    public function chunkSize(): int
    {
        return 20;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                NotificationService::send(
                    $this->authUserId,
                    'Import Completed',
                    'Category import processing finished successfully.',
                    'success'
                );
            },
        ];
    }
}
