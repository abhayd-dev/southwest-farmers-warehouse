<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareRole;
use App\Models\WarePermission;

/**
 * Enquiry escalation flow: Store -> Warehouse -> Main Super Admin.
 * manage_enquiries gates the Warehouse-side Enquiries screen.
 */
class EnquiriesPermissionSeeder extends Seeder
{
    public function run()
    {
        $permission = WarePermission::firstOrCreate(['name' => 'manage_enquiries', 'guard_name' => 'web']);

        WareRole::whereIn('name', ['Super Admin', 'VP Operations', 'Warehouse Manager'])
            ->get()
            ->each(fn ($role) => $role->permissions()->syncWithoutDetaching([$permission->id]));

        $this->command?->info('manage_enquiries permission ready.');
    }
}
