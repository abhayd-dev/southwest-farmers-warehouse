<?php

namespace App\Services;

use App\Models\RecallRequest;
use App\Models\ProductBatch;
use App\Models\StoreStock;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RecallService
{
    public function createRecall(array $data)
    {
        return DB::transaction(function () use ($data) {
            return RecallRequest::create([
                'store_id' => $data['store_id'],
                'product_id' => $data['product_id'],
                'requested_quantity' => $data['requested_quantity'],
                'reason' => $data['reason'],
                'reason_remarks' => $data['reason_remarks'] ?? null,
                'initiated_by' => Auth::id(),
                'status' => RecallRequest::STATUS_PENDING_STORE_APPROVAL,
            ]);
        });
    }

    public function processDispatch(RecallRequest $recall, array $batches)
    {
        DB::transaction(function () use ($recall, $batches) {
            $total = 0;

            foreach ($batches as $batch) {
                ProductBatch::find($batch['batch_id'])->decrement('remaining_quantity', $batch['quantity']);
                $total += $batch['quantity'];

                StockTransaction::create([
                    'product_id' => $recall->product_id,
                    'product_batch_id' => $batch['batch_id'],
                    'store_id' => $recall->store_id,
                    'type' => 'recall_out',
                    'quantity_change' => -$batch['quantity'],
                    'running_balance' => StoreStock::where('store_id', $recall->store_id)->where('product_id', $recall->product_id)->first()->quantity,
                    'reference_id' => 'RECALL-' . $recall->id,
                ]);
            }

            StoreStock::where('store_id', $recall->store_id)
                ->where('product_id', $recall->product_id)
                ->decrement('quantity', $total);

            $recall->update([
                'dispatched_quantity' => $total,
                'status' => RecallRequest::STATUS_DISPATCHED,
            ]);
        });
    }

    public function processReceive(RecallRequest $recall, int $quantity, string $remarks = null)
    {
        DB::transaction(function () use ($recall, $quantity, $remarks) {
            ProductStock::where('warehouse_id', 1)
                ->where('product_id', $recall->product_id)
                ->increment('quantity', $quantity);

            StockTransaction::create([
                'product_id' => $recall->product_id,
                'store_id' => $recall->store_id,
                'type' => 'recall_in',
                'quantity_change' => $quantity,
                'running_balance' => ProductStock::where('product_id', $recall->product_id)->first()->quantity,
                'ware_user_id' => Auth::id(),
                'reference_id' => 'RECALL-' . $recall->id,
                'remarks' => $remarks,
            ]);

            $recall->update([
                'received_quantity' => $quantity,
                'received_by_ware_user_id' => Auth::id(),
                'warehouse_remarks' => $remarks,
                'status' => $quantity >= $recall->dispatched_quantity ? RecallRequest::STATUS_COMPLETED : RecallRequest::STATUS_RECEIVED,
            ]);
        });
    }
}