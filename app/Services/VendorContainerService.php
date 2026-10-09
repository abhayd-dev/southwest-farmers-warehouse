<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\VendorContainerBalance;
use App\Models\VendorContainerTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Client ticket 23: returnable pallet/divider inventory per vendor.
 * Pallets/dividers received alongside a shipment add to that vendor's
 * balance; sending empties back to the vendor depletes it (refused if it
 * would go negative); reconciliation lets an admin correct the running
 * count directly, logging the delta for an audit trail.
 */
class VendorContainerService
{
    /** Read-only: an unsaved zero balance when the vendor has none yet, so viewing a page never writes. */
    public function getBalance(Vendor $vendor): VendorContainerBalance
    {
        return VendorContainerBalance::firstOrNew(['vendor_id' => $vendor->id], ['pallet_count' => 0, 'divider_count' => 0]);
    }

    public function receiveOnPO(PurchaseOrder $po, float $palletQty, float $dividerQty, string $by): void
    {
        $palletQty = round($palletQty, 2);
        $dividerQty = round($dividerQty, 2);
        if ($palletQty <= 0 && $dividerQty <= 0) {
            return;
        }

        DB::transaction(function () use ($po, $palletQty, $dividerQty, $by) {
            $vendor = $po->vendor()->lockForUpdate()->first();
            if (!$vendor) {
                return;
            }
            $balance = VendorContainerBalance::lockForUpdate()->firstOrCreate(['vendor_id' => $vendor->id], ['pallet_count' => 0, 'divider_count' => 0]);

            if ($palletQty > 0) {
                $balance->pallet_count += $palletQty;
                $this->logTransaction($vendor->id, 'pallet', 'received', $palletQty, $balance->pallet_count, $po->id, $by, "Received with PO# {$po->po_number}");
            }
            if ($dividerQty > 0) {
                $balance->divider_count += $dividerQty;
                $this->logTransaction($vendor->id, 'divider', 'received', $dividerQty, $balance->divider_count, $po->id, $by, "Received with PO# {$po->po_number}");
            }
            $balance->save();
        });
    }

    public function returnToVendor(Vendor $vendor, float $palletQty, float $dividerQty, string $by, ?PurchaseOrder $po = null, ?string $remarks = null): void
    {
        $palletQty = round($palletQty, 2);
        $dividerQty = round($dividerQty, 2);
        if ($palletQty <= 0 && $dividerQty <= 0) {
            throw new BusinessRuleException('Enter a Pallet or Divider count to send back.');
        }

        DB::transaction(function () use ($vendor, $palletQty, $dividerQty, $by, $po, $remarks) {
            $balance = VendorContainerBalance::lockForUpdate()->firstOrCreate(['vendor_id' => $vendor->id], ['pallet_count' => 0, 'divider_count' => 0]);

            if ($palletQty > ($balance->pallet_count + 0.001)) {
                throw new BusinessRuleException("Only {$balance->pallet_count} pallet(s) on hand for {$vendor->name} -- cannot send back {$palletQty}.");
            }
            if ($dividerQty > ($balance->divider_count + 0.001)) {
                throw new BusinessRuleException("Only {$balance->divider_count} divider(s) on hand for {$vendor->name} -- cannot send back {$dividerQty}.");
            }

            $note = $po ? "Returned with PO# {$po->po_number}" : 'Returned to vendor';
            if ($remarks) {
                $note .= ": {$remarks}";
            }

            if ($palletQty > 0) {
                $balance->pallet_count -= $palletQty;
                $this->logTransaction($vendor->id, 'pallet', 'returned', -$palletQty, $balance->pallet_count, $po?->id, $by, $note);
            }
            if ($dividerQty > 0) {
                $balance->divider_count -= $dividerQty;
                $this->logTransaction($vendor->id, 'divider', 'returned', -$dividerQty, $balance->divider_count, $po?->id, $by, $note);
            }
            $balance->save();
        });
    }

    public function reconcile(Vendor $vendor, float $newPalletCount, float $newDividerCount, string $by, string $reason): void
    {
        $newPalletCount = round($newPalletCount, 2);
        $newDividerCount = round($newDividerCount, 2);

        DB::transaction(function () use ($vendor, $newPalletCount, $newDividerCount, $by, $reason) {
            $balance = VendorContainerBalance::lockForUpdate()->firstOrCreate(['vendor_id' => $vendor->id], ['pallet_count' => 0, 'divider_count' => 0]);

            $palletDelta = round($newPalletCount - $balance->pallet_count, 2);
            $dividerDelta = round($newDividerCount - $balance->divider_count, 2);

            if (abs($palletDelta) > 0.001) {
                $balance->pallet_count = $newPalletCount;
                $this->logTransaction($vendor->id, 'pallet', 'adjustment', $palletDelta, $newPalletCount, null, $by, "Reconciliation: {$reason}");
            }
            if (abs($dividerDelta) > 0.001) {
                $balance->divider_count = $newDividerCount;
                $this->logTransaction($vendor->id, 'divider', 'adjustment', $dividerDelta, $newDividerCount, null, $by, "Reconciliation: {$reason}");
            }
            $balance->save();
        });
    }

    private function logTransaction(int $vendorId, string $type, string $direction, float $change, float $runningBalance, ?int $poId, string $by, string $remarks): void
    {
        VendorContainerTransaction::create([
            'vendor_id' => $vendorId,
            'container_type' => $type,
            'direction' => $direction,
            'quantity_change' => $change,
            'running_balance' => $runningBalance,
            'purchase_order_id' => $poId,
            'ware_user_id' => Auth::id(),
            'remarks' => "{$remarks} (by {$by})",
        ]);
    }
}
