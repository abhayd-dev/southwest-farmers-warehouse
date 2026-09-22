<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Services\EmailVerificationService;
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

            // Verify the approval email address, if one was provided
            if ($po->approval_email) {
                app(EmailVerificationService::class)->send(
                    'po_approval',
                    $po,
                    "Purchase Order #{$po->po_number} approvals"
                );
            }

            return $po;
        });
    }

    public function updatePO(PurchaseOrder $po, $data)
    {
        return DB::transaction(function () use ($po, $data) {
            $oldApprovalEmail = $po->approval_email;

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

            // Re-verify only if the approval email actually changed
            app(EmailVerificationService::class)->handleEmailChange(
                'po_approval',
                $po,
                $oldApprovalEmail,
                "Purchase Order #{$po->po_number} approvals"
            );

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
        $shortageItemIds = [];

        $po = DB::transaction(function () use ($poId, $receivedItems, $invoiceNumber, $duties, $shippingCost, $taxes, $transportationCost, $demurrage, &$shortageItemIds) {
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
                $poItem = $data['poItemModel'];

                if ($qtyToReceive <= 0) {
                    // Nothing arrived on this line in this shipment -- still
                    // counts toward whether the PO as a whole is complete
                    // (previously this line was skipped entirely here, so a
                    // PO with one item fully received and another left at 0
                    // could be wrongly marked "completed"), but it's not a
                    // "shortage" worth emailing the Purchase Manager about:
                    // it just hasn't arrived yet, unlike a line that arrived
                    // this round short of what was ordered.
                    if ($poItem->received_quantity < $poItem->requested_quantity) {
                        $allCompleted = false;
                    }
                    continue;
                }

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

                // Check for cost increase (Bypassed to allow cost updates)
                if ($poItem->product) {
                    $currentCost = floatval($poItem->product->cost_price);
                    // if ($actualCost > $currentCost && !$po->cost_increase_approved) {
                    //     throw new \Exception("CostIncreaseException");
                    // }
                }

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
                            route('warehouse.products.edit', $poItem->product_id)
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

                // Client feedback 9/21, items 1-2: a shipment can arrive over
                // or under what was ordered (e.g. 125 against an order of
                // 100, or 75 with nothing further coming). The warehouse
                // isn't sending the excess back or waiting on the shortfall,
                // so the Ordered Qty itself gets corrected to match what
                // actually came in -- this is also what the invoice total
                // below is based on, and it's what lets a short shipment
                // complete the order below instead of sitting "partial"
                // forever waiting on a remainder that was never coming.
                $orderedQtyOverride = $data['ordered_qty'] ?? null;
                if ($orderedQtyOverride !== null && $orderedQtyOverride !== '') {
                    $poItem->requested_quantity = max(0, intval($orderedQtyOverride));
                }

                $poItem->save();

                if ($poItem->received_quantity < $poItem->requested_quantity) {
                    $allCompleted = false;
                    // Shortage: less arrived (so far) than was requested on this line.
                    $shortageItemIds[] = $poItem->id;
                }
            }

            // Invoice total now reflects the (possibly corrected) ordered
            // quantities above, not what was originally keyed in when the PO
            // was created.
            $po->total_amount = $po->items()->get()->sum(fn ($i) => $i->requested_quantity * $i->unit_cost);

            $po->status = $allCompleted ? PurchaseOrder::STATUS_COMPLETED : PurchaseOrder::STATUS_PARTIAL;
            // Reset approval flag for future partial receipts
            $po->cost_increase_approved = false;
            $po->save();

            if ($allCompleted && $po->vendor) {
                $po->vendor->updateRating();
            }


            return $po;
        });

        // Notify Purchase Manager(s) of any shortfall on this receipt — sent only
        // after the transaction commits, so a mail hiccup can never roll back a receiving.
        if (!empty($shortageItemIds)) {
            app(\App\Services\PurchaseManagerNotificationService::class)->notifyShortage($po, $shortageItemIds);
        }

        return $po;
    }
}
