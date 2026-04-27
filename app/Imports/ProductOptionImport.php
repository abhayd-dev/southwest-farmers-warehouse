<?php

namespace App\Imports;

use App\Models\ProductOption;
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

class ProductOptionImport implements ToCollection, WithHeadingRow, ShouldQueue, WithChunkReading, WithBatchInserts, WithEvents
{
    use TracksImportProgress;

    protected $categoryId;
    protected $subcategoryId;
    protected $authUserId;

    public function __construct($categoryId, $subcategoryId = null, $authUserId = null, $importTaskId = null)
    {
        $this->categoryId    = $categoryId;
        $this->subcategoryId = $subcategoryId;
        $this->authUserId    = $authUserId ?? Auth::id();
        $this->importTaskId = $importTaskId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            ProductOption::create([
                'ware_user_id'    => $this->authUserId,
                'category_id'    => $this->categoryId,
                'subcategory_id' => $this->subcategoryId,
                'option_name'    => $row['option_name'],
                'sku'            => $row['sku'] ?? null,
                'unit'           => $row['unit'] ?? null,
                'tax_percent'    => (float)($row['tax_percent'] ?? 0),
                'cost_price'     => (float)($row['cost_price'] ?? 0),
                'base_price'     => (float)($row['base_price'] ?? 0),
                'mrp'            => (float)($row['mrp'] ?? 0),
                'is_active'      => true,
                'store_id'       => null,
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
                    'Product option import processing finished successfully.',
                    'success'
                );
            },
        ];
    }
}

