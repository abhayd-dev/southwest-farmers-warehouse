<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchaseOrderService
{
    public function createPO($data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Header
            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
                'vendor_id' => $data['vendor_id'],
                'warehouse_id' => 1, // Default Central
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'approval_email' => $data['approval_email'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'created_by' => Auth::id(),
            ]);

            $grandTotal = 0;

            // 2. Create Items
            foreach ($data['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['cost']); 

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'requested_quantity' => $item['quantity'],
                    'unit_cost' => $item['cost'],
                    'total_cost' => $lineTotal
                ]);

                $grandTotal += $lineTotal;
            }

            // 3. Update Total
            $po->update(['total_amount' => $grandTotal]);

            return $po;
        });
    }

    public function receiveItems($poId, $receivedItems, $invoiceNumber = null)
    {
        return DB::transaction(function () use ($poId, $receivedItems, $invoiceNumber) {
            $po = PurchaseOrder::findOrFail($poId);
            $allCompleted = true;

            foreach ($receivedItems as $itemId => $data) {
                $qtyToReceive = intval($data['receive_qty']);

                if ($qtyToReceive <= 0) continue;

                $poItem = PurchaseOrderItem::findOrFail($itemId);

                // Generate batch number if not provided
                $batchNumber = $data['batch_number'] ?? null;
                if (empty($batchNumber)) {
                    $batchNumber = 'BATCH-' . date('Ymd') . '-' . str_pad($poItem->product_id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
                }

                $batch = ProductBatch::create([
                    'product_id' => $poItem->product_id,
                    'warehouse_id' => 1,
                    'batch_number' => $batchNumber,
                    'manufacturing_date' => $data['mfg_date'] ?? null,
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'cost_price' => $poItem->unit_cost,
                    'quantity' => $qtyToReceive,
                    'is_active' => true
                ]);

                $stock = ProductStock::where('product_id', $poItem->product_id)
                    ->where('warehouse_id', 1)
                    ->first();

                if ($stock) {
                    $stock->quantity += $qtyToReceive;
                    $stock->save(); 
                } else {
                    $stock = ProductStock::create([
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => 1,
                        'quantity' => $qtyToReceive
                    ]);
                }

                StockTransaction::create([
                    'product_id' => $poItem->product_id,
                    'warehouse_id' => 1,
                    'product_batch_id' => $batch->id,
                    'type' => 'purchase_in',
                    'quantity_change' => $qtyToReceive,
                    'running_balance' => $stock->quantity,
                    'ware_user_id' => Auth::id(),
                    'reference_id' => $po->po_number,
                    'remarks' => "Inv# " . ($invoiceNumber ?? 'N/A')
                ]);

                $poItem->received_quantity += $qtyToReceive;
                $poItem->save();

                if ($poItem->received_quantity < $poItem->requested_quantity) {
                    $allCompleted = false;
                }
            }

            $po->status = $allCompleted ? PurchaseOrder::STATUS_COMPLETED : PurchaseOrder::STATUS_PARTIAL;
            $po->save();

            if ($allCompleted && $po->vendor) {
                $po->vendor->updateRating();
            }
          

            return $po;
        });
    }
}