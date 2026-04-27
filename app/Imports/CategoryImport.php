<?php

namespace App\Imports;

use App\Models\ProductCategory;
use App\Services\NotificationService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use App\Traits\TracksImportProgress;

class CategoryImport implements ToCollection, WithHeadingRow, ShouldQueue, WithChunkReading, WithBatchInserts, WithEvents
{
    use TracksImportProgress;

    protected $authUserId;

    public function __construct($authUserId = null, $importTaskId = null)
    {
        $this->authUserId = $authUserId ?? Auth::id();
        $this->importTaskId = $importTaskId;
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

        $this->updateImportProgress($rows->count());
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function registerEvents(): array
    {
        $progressEvents = $this->getImportProgressEvents();

        return [
            BeforeImport::class => $progressEvents[\Maatwebsite\Excel\Events\BeforeImport::class] ?? null,
            AfterImport::class => function (AfterImport $event) use ($progressEvents) {
                // Run progress completion logic
                if (isset($progressEvents[AfterImport::class])) {
                    $progressEvents[AfterImport::class]($event);
                }

                // Run custom notification logic
                NotificationService::send(
                    $this->authUserId,
                    'Import Completed',
                    'Processing finished successfully.',
                    'success'
                );
            },
        ];
    }
}
