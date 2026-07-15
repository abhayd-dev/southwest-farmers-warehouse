<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all duplicate vendor names
        $duplicateNames = DB::table('vendors')
            ->select('name')
            ->whereNull('deleted_at')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicateNames as $name) {
            // Find all vendors with this name (case-insensitive)
            $vendors = Vendor::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->orderBy('id', 'asc')
                ->get();

            if ($vendors->count() > 1) {
                // Keep the first one (lowest ID)
                $keep = $vendors->shift();

                // Delete the rest
                foreach ($vendors as $duplicateVendor) {
                    // Re-associate any Purchase Orders referencing the duplicate vendor to point to the kept vendor
                    DB::table('purchase_orders')
                        ->where('vendor_id', $duplicateVendor->id)
                        ->update(['vendor_id' => $keep->id]);

                    // Purge duplicate vendor record
                    $duplicateVendor->forceDelete();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse action needed
    }
};
