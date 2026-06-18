<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductStock;
use App\Services\NotificationService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\TracksImportProgress;

class ProductImport implements ToCollection, WithHeadingRow, ShouldQueue, WithChunkReading, WithBatchInserts, WithEvents
{
    use RemembersRowNumber, TracksImportProgress;

    protected $departmentId;
    protected $categoryId;
    protected $subcategoryId;
    protected $authUserId;

    public function __construct($categoryId, $subcategoryId, $departmentId, $authUserId = null, $importTaskId = null)
    {
        $this->categoryId = $categoryId;
        $this->subcategoryId = $subcategoryId;
        $this->departmentId = $departmentId;
        $this->authUserId = $authUserId ?? Auth::id();
        $this->importTaskId = $importTaskId;
    }

    public function collection(Collection $rows)
    {
        $skippedCount = 0;

        foreach ($rows as $row) {
            if (empty($row['product_name'])) continue;

            $barcode = (string)($row['barcode'] ?? '');

            // ── DUPLICATE GUARD ──────────────────────────────────────────────
            // Skip this row if a warehouse product with the same barcode already
            // exists. This prevents duplicates when the same file is uploaded
            // multiple times or a queued job retries.
            if ($barcode !== '' && Product::whereNull('store_id')->where('barcode', $barcode)->exists()) {
                \Illuminate\Support\Facades\Log::warning('ProductImport: skipped duplicate barcode', [
                    'barcode'      => $barcode,
                    'product_name' => $row['product_name'],
                    'task_id'      => $this->importTaskId,
                ]);
                $skippedCount++;
                continue;
            }

            DB::transaction(function () use ($row, $barcode) {
                // Reuse existing ProductOption for this barcode if one already
                // exists (e.g. from a previous partial import), otherwise create.
                $option = ProductOption::firstOrCreate(
                    ['barcode' => $barcode, 'store_id' => null],
                    [
                        'ware_user_id'               => $this->authUserId,
                        'option_name'                => $row['product_name'],
                        'sku'                        => $row['sku'] ?? null,
                        'category_id'                => $this->categoryId,
                        'subcategory_id'             => $this->subcategoryId,
                        'unit'                       => $row['unit'],
                        'upc'                        => (string)($row['upc'] ?? ''),
                        'plu_code'                   => (string)($row['plu_code'] ?? ''),
                        'warehouse_markup_percentage' => (float)($row['warehouse_markup_percentage'] ?? 0),
                        'cost_price'                 => (float)($row['cost_price'] ?? 0),
                        'base_price'                 => (float)($row['price'] ?? 0),
                        'mrp'                        => (float)($row['price'] ?? 0),
                        'is_active'                  => 1,
                        'department_id'              => $this->departmentId,
                    ]
                );

                $product = Product::create([
                    'ware_user_id'               => $this->authUserId,
                    'product_option_id'          => $option->id,
                    'department_id'              => $this->departmentId,
                    'category_id'                => $this->categoryId,
                    'subcategory_id'             => $this->subcategoryId,
                    'product_name'               => $row['product_name'],
                    'sku'                        => $row['sku'] ?? null,
                    'unit'                       => $row['unit'],
                    'barcode'                    => $barcode,
                    'upc'                        => (string)($row['upc'] ?? ''),
                    'plu_code'                   => (string)($row['plu_code'] ?? ''),
                    'units_per_carton'           => (int)($row['units_per_carton'] ?? 1),
                    'cost_price'                 => (float)($row['cost_price'] ?? 0),
                    'warehouse_markup_percentage' => (float)($row['warehouse_markup_percentage'] ?? 0),
                    'price'                      => (float)($row['price'] ?? 0),
                    'store_markup_percentage'    => (float)($row['store_markup_percentage'] ?? 0),
                    'store_retail_price'         => (float)($row['store_retail_price'] ?? 0),
                    'manual_override_price'      => isset($row['manual_override_price']) && $row['manual_override_price'] !== ''
                        ? (float)$row['manual_override_price'] : null,
                    'is_active'                  => 1,
                    'store_id'                   => null,
                ]);

                ProductStock::create([
                    'product_id'   => $product->id,
                    'warehouse_id' => 1,
                    'quantity'     => 0,
                ]);
            });
        }

        $this->updateImportProgress($rows->count());

        if ($skippedCount > 0) {
            \Illuminate\Support\Facades\Log::info("ProductImport: {$skippedCount} duplicate row(s) skipped in this chunk.", [
                'task_id' => $this->importTaskId,
            ]);
        }
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
                    'Product import processing finished successfully.',
                    'success'
                );
            },
        ];
    }
}
