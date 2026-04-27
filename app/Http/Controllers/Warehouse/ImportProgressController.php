<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ImportTask;
use Illuminate\Http\Request;

class ImportProgressController extends Controller
{
    public function show($id)
    {
        $task = ImportTask::findOrFail($id);

        return response()->json([
            'id' => $task->id,
            'status' => $task->status,
            'total_rows' => $task->total_rows,
            'processed_rows' => $task->processed_rows,
            'percentage' => $task->percentage,
            'status_message' => $task->status_message,
            'error_message' => $task->error_message,
        ]);
    }
}
