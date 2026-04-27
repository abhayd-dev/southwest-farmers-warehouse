<?php

namespace App\Traits;

use App\Models\ImportTask;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

trait TracksImportProgress
{
    protected $importTaskId;

    public function setImportTaskId($id)
    {
        $this->importTaskId = $id;
        return $this;
    }

    public function getImportProgressEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $totalRows = 0;
                $sheetCounts = $event->getReader()->getTotalRows();
                
                // getTotalRows returns [sheet_name => count]
                foreach ($sheetCounts as $count) {
                    // Subtract 1 for heading row if applicable
                    $totalRows += max(0, $count - 1);
                }

                ImportTask::where('id', $this->importTaskId)->update([
                    'total_rows' => $totalRows,
                    'status' => ImportTask::STATUS_PROCESSING,
                    'status_message' => 'Analyzing file and preparing rows...',
                ]);
            },
            AfterImport::class => function (AfterImport $event) {
                $task = ImportTask::find($this->importTaskId);
                if ($task) {
                    $task->update([
                        'processed_rows' => $task->total_rows,
                        'status' => ImportTask::STATUS_COMPLETED,
                        'status_message' => 'Import finished successfully!',
                    ]);
                }
            },
            ImportFailed::class => function (ImportFailed $event) {
                ImportTask::where('id', $this->importTaskId)->update([
                    'status' => ImportTask::STATUS_FAILED,
                    'error_message' => $event->getException()->getMessage(),
                ]);
            },
        ];
    }

    protected function updateImportProgress($processedCount)
    {
        $task = ImportTask::find($this->importTaskId);
        if ($task) {
            $task->increment('processed_rows', $processedCount);
            $task->update(['status_message' => "Imported {$task->processed_rows} of {$task->total_rows} rows..."]);
        }
    }
}
