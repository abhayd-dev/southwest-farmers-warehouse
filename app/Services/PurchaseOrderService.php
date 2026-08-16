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
                'approver_phone' => $data['approver_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'vendor_notes' => $data['vendor_notes'] ?? null,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'approval_status' => 'draft',
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

    public function updatePO(PurchaseOrder $po, $data)
    {
        return DB::transaction(function () use ($po, $data) {
            // 1. Update Header
            $po->update([
                'vendor_id' => $data['vendor_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'approval_email' => $data['approval_email'] ?? null,
                'approver_phone' => $data['approver_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'vendor_notes' => $data['vendor_notes'] ?? null,
            ]);

            // 2. Re-create Items (Delete existing and insert new ones)
            $po->items()->delete();

            $grandTotal = 0;
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

    public function receiveItems($poId, $receivedItems, $invoiceNumber = null, $duties = 0, $shippingCost = 0, $taxes = 0, $transportationCost = 0, $demurrage = 0)
    {
        return DB::transaction(function () use ($poId, $receivedItems, $invoiceNumber, $duties, $shippingCost, $taxes, $transportationCost, $demurrage) {
            $po = PurchaseOrder::findOrFail($poId);

            // Update additional costs and invoice number
            $po->update([
                'vendor_invoice_number' => $invoiceNumber ?? $po->vendor_invoice_number,
                'duties' => $duties,
                'shipping_cost' => $shippingCost,
                'taxes' => $taxes,
                'transportation_cost' => $transportationCost,
                'demurrage' => $demurrage,
            ]);

            $allCompleted = true;
            $productIds = [];
            foreach ($receivedItems as $itemId => $data) {
                $poItem = PurchaseOrderItem::findOrFail($itemId);
                $productIds[] = $poItem->product_id;
                // Add product_id to data for later loop
                $receivedItems[$itemId]['product_id'] = $poItem->product_id;
                $receivedItems[$itemId]['poItemModel'] = $poItem;
            }

            // Pre-fetch warehouse stocks
            $warehouseStocks = ProductStock::whereIn('product_id', $productIds)
                ->where('warehouse_id', 1)
                ->get()->keyBy('product_id');

            $duties = floatval($duties ?? 0);
            $shipping = floatval($shippingCost ?? 0);
            $taxes = floatval($taxes ?? 0); // Brokerage Fee / Taxes
            $transportation = floatval($transportationCost ?? 0);
            $demurrageCost = floatval($demurrage ?? 0);

            $totalReceivedQty = array_sum(array_map(fn($item) => intval($item['receive_qty'] ?? 0), $receivedItems));
            $landedFeePerUnit = $totalReceivedQty > 0 ? ($duties + $shipping + $taxes + $transportation + $demurrageCost) / $totalReceivedQty : 0;

            foreach ($receivedItems as $itemId => $data) {
                $qtyToReceive = intval($data['receive_qty'] ?? 0);

                if ($qtyToReceive <= 0) continue;

                $poItem = $data['poItemModel'];

                // Generate batch number if not provided
                $batchNumber = $data['batch_number'] ?? null;
                if (empty($batchNumber)) {
                    $batchNumber = 'BATCH-' . date('Ymd') . '-' . str_pad($poItem->product_id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
                }

                $poPrice = floatval($data['receiving_price'] ?? $poItem->unit_cost);
                if ($poPrice <= 0) {
                    $poPrice = floatval($poItem->unit_cost);
                }

                // Actual Cost = ((Duties + Brokerage Fee + Shipping) / Total Received Qty) + PO Price
                $actualCost = round($poPrice + $landedFeePerUnit, 2);

                $batch = ProductBatch::create([
                    'product_id' => $poItem->product_id,
                    'warehouse_id' => 1,
                    'batch_number' => $batchNumber,
                    'manufacturing_date' => $data['mfg_date'] ?? null,
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'cost_price' => $actualCost,
                    'quantity' => $qtyToReceive,
                    'is_active' => true
                ]);

                // Update product catalog cost_price with Actual Cost & send notification
                if ($actualCost > 0 && $poItem->product) {
                    $oldCost = floatval($poItem->product->cost_price);
                    $poItem->product->update(['cost_price' => $actualCost]);

                    if (abs($oldCost - $actualCost) > 0.01) {
                        NotificationService::sendToAdmins(
                            'Product Cost Updated',
                            "Cost for {$poItem->product->product_name} updated from $" . number_format($oldCost, 2) . " to $" . number_format($actualCost, 2) . " (PO #{$po->po_number})",
                            'info',
                            route('warehouse.products.show', $poItem->product_id)
                        );
                    }
                }

                $stock = $warehouseStocks->get($poItem->product_id);

                if ($stock) {
                    $stock->quantity += $qtyToReceive;
                    $stock->save();
                } else {
                    $stock = ProductStock::create([
                        'product_id' => $poItem->product_id,
                        'warehouse_id' => 1,
                        'quantity' => $qtyToReceive
                    ]);
                    $warehouseStocks->put($poItem->product_id, $stock);
                }

                StockTransaction::create([
                    'product_id' => $poItem->product_id,
                    'warehouse_id' => 1,
                    'product_batch_id' => $batch->id,
                    'type' => 'purchase_in',
                    'quantity_change' => $qtyToReceive,
                    'running_balance' => $stock->quantity,
                    'ware_user_id' => Auth::id(),
                    'reference_id' => $po->id,
                    'reference_type' => 'App\Models\PurchaseOrder',
                    'remarks' => "PO# {$po->po_number} / Inv# " . ($invoiceNumber ?? 'N/A')
                ]);

                $poItem->received_quantity += $qtyToReceive;
                $poItem->receiving_unit_cost = $poPrice;
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
