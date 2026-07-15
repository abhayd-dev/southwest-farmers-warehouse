<?php

namespace App\Imports;

use App\Models\Vendor;
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
use Illuminate\Support\Facades\Log;

use App\Traits\TracksImportProgress;

class VendorImport implements ToCollection, WithHeadingRow, ShouldQueue, WithChunkReading, WithBatchInserts, WithEvents
{
    use TracksImportProgress;

    protected $authUserId;
    protected $skippedErrors = [];

    public function __construct($authUserId = null, $importTaskId = null)
    {
        $this->authUserId = $authUserId ?? Auth::id();
        $this->importTaskId = $importTaskId;
    }

    public function collection(Collection $rows)
    {
        $skippedCount = 0;
        $this->skippedErrors = [];

        foreach ($rows as $row) {
            // Skip rows with no vendor name
            if (empty($row['name'])) continue;

            $email = isset($row['email']) ? trim((string) $row['email']) : null;
            $phone = isset($row['phone']) ? trim((string) $row['phone']) : null;

            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            if ($name === '') continue;

            // ── DUPLICATE GUARD ───────────────────────────────────────────────
            // Skip if a vendor with the same name already exists (case-insensitive check).
            if (Vendor::whereRaw('LOWER(name) = ?', [strtolower($name)])->exists()) {
                Log::warning('VendorImport: skipped duplicate name', [
                    'name'    => $name,
                    'email'   => $email,
                    'phone'   => $phone,
                    'task_id' => $this->importTaskId,
                ]);
                $this->skippedErrors[] = "Row '{$name}' skipped: Duplicate vendor name already exists.";
                $skippedCount++;
                continue;
            }

            Vendor::create([
                'name'           => $name,
                'contact_person' => isset($row['contact_person']) ? trim($row['contact_person']) : null,
                'email'          => $email ?: null,
                'phone'          => $phone ?: null,
                'address'        => isset($row['address']) ? trim($row['address']) : null,
                'lead_time_days' => isset($row['lead_time_days']) && $row['lead_time_days'] !== ''
                                        ? (int) $row['lead_time_days'] : null,
                'is_active'      => 1,
            ]);
        }

        $this->updateImportProgress($rows->count());

        if ($skippedCount > 0 && !empty($this->skippedErrors)) {
            Log::info("VendorImport: {$skippedCount} duplicate row(s) skipped in this chunk.", [
                'task_id' => $this->importTaskId,
            ]);

            $task = \App\Models\ImportTask::find($this->importTaskId);
            if ($task) {
                $currentErrors = [];
                if ($task->error_message) {
                    $decoded = json_decode($task->error_message, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $currentErrors = $decoded;
                    } else {
                        $currentErrors = [$task->error_message];
                    }
                }
                $updatedErrors = array_merge($currentErrors, $this->skippedErrors);
                $task->update([
                    'error_message' => json_encode($updatedErrors),
                ]);
            }
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
            AfterImport::class  => function (AfterImport $event) use ($progressEvents) {
                // Run progress completion logic
                if (isset($progressEvents[AfterImport::class])) {
                    $progressEvents[AfterImport::class]($event);
                }

                // Notify the user
                NotificationService::send(
                    $this->authUserId,
                    'Import Completed',
                    'Vendor import processing finished successfully.',
                    'success'
                );
            },
        ];
    }
}
