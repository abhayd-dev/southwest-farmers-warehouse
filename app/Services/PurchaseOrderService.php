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
                'notes' => $data['notes'],
                'status' => PurchaseOrder::STATUS_DRAFT,
                'created_by' => Auth::id(),
            ]);

            $grandTotal = 0;

            // 2. Create Items
            foreach ($data['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['cost']); // Add tax logic if needed

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

                // Skip if quantity is 0
                if ($qtyToReceive <= 0) continue;

                $poItem = PurchaseOrderItem::findOrFail($itemId);

                // 1. Create Product Batch
                $batch = ProductBatch::create([
                    'product_id' => $poItem->product_id,
                    'warehouse_id' => 1,
                    'batch_number' => $data['batch_number'] ?? 'BAT-' . time() . '-' . $itemId,
                    'manufacturing_date' => $data['mfg_date'] ?? null,
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'cost_price' => $poItem->unit_cost,
                    'quantity' => $qtyToReceive,
                    'is_active' => true
                ]);

                // 2. Update Warehouse Stock (Optimized)
                // Use updateOrCreate instead of firstOrCreate + increment to save 1 query
                $stock = ProductStock::where('product_id', $poItem->product_id)
                    ->where('warehouse_id', 1)
                    ->first();

                if ($stock) {
                    $stock->quantity += $qtyToReceive;
                    $stock->save(); // Saves 1 round-trip compared to increment()
                } else {
                    $stock = ProductStock::create([
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => 1,
                        'quantity' => $qtyToReceive
                    ]);
                }

                // 3. Log Transaction
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

                // 4. Update Line Item
                $poItem->received_quantity += $qtyToReceive;
                $poItem->save();

                // Check if this item is fully received
                if ($poItem->received_quantity < $poItem->requested_quantity) {
                    $allCompleted = false;
                }
            }

            // 5. Update PO Status
            $po->status = $allCompleted ? PurchaseOrder::STATUS_COMPLETED : PurchaseOrder::STATUS_PARTIAL;
            $po->save();

            return $po;
        });
    }
}
