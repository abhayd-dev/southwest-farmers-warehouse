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

            // Verify the approval email address, if one was provided (skipped
            // when this approver already confirmed it on an earlier PO).
            if ($po->approval_email) {
                app(EmailVerificationService::class)->sendUnlessAlreadyVerified(
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

    public function receiveItems($poId, $receivedItems, $invoiceNumber = null, $duties = 0, $shippingCost = 0, $taxes = 0, $transportationCost = 0, $demurrage = 0, $invoiceDocument = null, ?string $shipmentType = null, bool $completeNow = false)
    {
        $shortageItemIds = [];
        $newOverReceipt = false;

        $raisedByReceiver = [];
        $po = DB::transaction(function () use ($poId, $receivedItems, $invoiceNumber, $duties, $shippingCost, $taxes, $transportationCost, $demurrage, $invoiceDocument, $shipmentType, $completeNow, &$shortageItemIds, &$newOverReceipt, &$raisedByReceiver) {
            $po = PurchaseOrder::findOrFail($poId);

            $updateData = [
                'vendor_invoice_number' => $invoiceNumber ?? $po->vendor_invoice_number,
                'duties' => $duties,
                'shipping_cost' => $shippingCost,
                'taxes' => $taxes,
                'transportation_cost' => $transportationCost,
                'demurrage' => $demurrage,
            ];

            if ($invoiceDocument) {
                $updateData['invoice_document'] = $invoiceDocument;
            }
            if ($shipmentType) {
                $updateData['shipment_type'] = $shipmentType;
            }

            // Update additional costs, invoice number, and attached invoice document
            $po->update($updateData);

            $allCompleted = true;
            $overLines = [];
            $raisedLines = [];
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

            $totalReceivedQty = array_sum(array_map(fn($item) => round((float) ($item['receive_qty'] ?? 0), 2), $receivedItems));
            $landedFeePerUnit = $totalReceivedQty > 0 ? ($duties + $shipping + $taxes + $transportation + $demurrageCost) / $totalReceivedQty : 0;

            foreach ($receivedItems as $itemId => $data) {
                $qtyToReceive = round((float) ($data['receive_qty'] ?? 0), 2);
                $poItem = $data['poItemModel'];

                if ($qtyToReceive <= 0) {
                    // An Ordered Qty edit counts even on a line where nothing arrived
                    // this time (e.g. set to 0: none of it is coming).
                    $orderedOnlyEdit = $data['ordered_qty'] ?? null;
                    if ($orderedOnlyEdit !== null && $orderedOnlyEdit !== '' && round((float) $orderedOnlyEdit, 2) != (float) $poItem->requested_quantity) {
                        $poItem->requested_quantity = max(0, round((float) $orderedOnlyEdit, 2));
                        $poItem->save();
                    }
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

                $orderedBeforeThisReceipt = (float) $poItem->requested_quantity;
                $poItem->received_quantity += $qtyToReceive;
                $poItem->receiving_unit_cost = $poPrice;

                // Client feedback 9/21, items 1-2: a shipment can arrive over
                // or under what was ordered (e.g. 125 against an order of
                // 100, or 75 with nothing further coming). The Ordered Qty
                // can be corrected on this screen to match what actually came
                // in -- that is what the invoice total below is based on.
                $orderedQtyOverride = $data['ordered_qty'] ?? null;
                if ($orderedQtyOverride !== null && $orderedQtyOverride !== '') {
                    $poItem->requested_quantity = max(0, round((float) $orderedQtyOverride, 2));
                    if ((float) $poItem->requested_quantity > $orderedBeforeThisReceipt) {
                        $raisedLines[] = ($poItem->product->product_name ?? ('Product #' . $poItem->product_id))
                            . ' ' . $this->qty($orderedBeforeThisReceipt) . ' -> ' . $this->qty($poItem->requested_quantity);
                    }
                }

                // Client PDF 9/24, item 1: more arrived than ordered is still
                // received, but flagged for the approver (OverReceiptService).
                // Client 9/28: raising Ordered Qty here IS the approval, so only
                // what arrived beyond the (possibly raised) Ordered Qty is flagged.
                if ($poItem->received_quantity > (float) $poItem->requested_quantity) {
                    $overLines[$poItem->id] = [
                        'item_id' => $poItem->id,
                        'product' => $poItem->product->product_name ?? ('Product #' . $poItem->product_id),
                        'ordered' => (float) $poItem->requested_quantity,
                        'received' => (float) $poItem->received_quantity,
                        'unit_cost' => (float) $poItem->unit_cost,
                    ];
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

            // Client PDF 9/24, items 2-3: a short receipt off a container
            // leaves the order open (shown as "In Transit"); off a truck the
            // rest isn't coming, so the order is closed.
            if ($allCompleted || $shipmentType === PurchaseOrder::SHIPMENT_TRUCK || $completeNow) {
                $po->status = PurchaseOrder::STATUS_COMPLETED;
                // Closed with lines still short (truck, or "nothing more is coming"):
                // the invoice becomes what was actually received.
                if (! $allCompleted) {
                    $this->applyShortClose($po, Auth::user()->name ?? 'System');
                }
            } else {
                $po->status = PurchaseOrder::STATUS_PARTIAL;
            }

            if ($overLines) {
                // Keep lines from an earlier, still-undecided over-receipt.
                $existing = $po->over_receipt_status === PurchaseOrder::OVER_RECEIPT_PENDING
                    ? collect($po->over_receipt_lines ?? [])->keyBy('item_id')->all()
                    : [];
                foreach ($overLines as $id => $line) {
                    if (isset($existing[$id])) {
                        $line['ordered'] = $existing[$id]['ordered'];
                    }
                    $existing[$id] = $line;
                }
                $po->over_receipt_lines = array_values($existing);
                $po->over_receipt_status = PurchaseOrder::OVER_RECEIPT_PENDING;
                $po->over_receipt_decided_by = null;
                $po->over_receipt_decided_at = null;
                $newOverReceipt = true;
            }
            $raisedByReceiver = $raisedLines;

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

        if ($newOverReceipt) {
            app(\App\Services\OverReceiptService::class)->requestApproval($po);
        }

        // The Ordered Qty raise counted as the approval: leave a record of who did it.
        if ($raisedByReceiver) {
            NotificationService::sendToAdmins(
                'Ordered Qty raised while receiving',
                "PO #{$po->po_number}: " . (Auth::user()->name ?? 'System') . ' raised Ordered Qty (counts as approval, invoice updated): ' . implode('; ', $raisedByReceiver) . '.',
                'info',
                route('warehouse.purchase-orders.show', $po->id)
            );
        }

        return $po;
    }
    /**
     * Client 9/27: complete an order that came in short with nothing more
     * coming -- used by "Close order" on a partial PO.
     *
     * @return array the short lines (ordered vs received), empty if none were short
     */
    public function closeShort(PurchaseOrder $po, string $closedBy): array
    {
        return DB::transaction(function () use ($po, $closedBy) {
            $po = PurchaseOrder::lockForUpdate()->findOrFail($po->id);
            $lines = $this->applyShortClose($po, $closedBy);
            $po->status = PurchaseOrder::STATUS_COMPLETED;
            $po->save();

            return $lines;
        });
    }

    /**
     * Every line received short gets its invoice quantity (requested_quantity)
     * set to what was received, and the invoice total is recalculated -- so
     * payment covers only what arrived. The original ordered quantities are
     * kept in short_close_lines. The caller saves $po.
     */
    private function applyShortClose(PurchaseOrder $po, string $closedBy): array
    {
        $lines = [];
        foreach ($po->items()->with('product')->get() as $item) {
            if ((float) $item->received_quantity >= (float) $item->requested_quantity) {
                continue;
            }
            $lines[] = [
                'item_id' => $item->id,
                'product' => $item->product->product_name ?? ('Product #' . $item->product_id),
                'ordered' => (float) $item->requested_quantity,
                'received' => (float) $item->received_quantity,
                'unit_cost' => (float) $item->unit_cost,
            ];
            $item->requested_quantity = (float) $item->received_quantity;
            $item->save();
        }

        $po->total_amount = $po->items()->get()->sum(fn ($i) => $i->requested_quantity * $i->unit_cost);
        if ($lines) {
            $po->short_close_lines = $lines;
            $po->short_closed_by = $closedBy;
            $po->short_closed_at = now();
        }

        return $lines;
    }

    private function qty($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
