<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WareRole;
use App\Models\WarePermission;

/**
 * The sidebar already gated a "Promotions" link behind manage_promotions,
 * but the permission itself was never created — meaning only Super Admin
 * (who bypasses permission checks) could ever see it, and it pointed at a
 * dead href="#" link besides. This seeds the permission properly now that
 * the page behind it actually exists.
 */
class PromotionsPermissionSeeder extends Seeder
{
    public function run()
    {
        $permission = WarePermission::firstOrCreate(['name' => 'manage_promotions', 'guard_name' => 'web']);

        WareRole::whereIn('name', ['Super Admin', 'VP Operations', 'Purchase Manager', 'Warehouse Manager'])
            ->get()
            ->each(fn ($role) => $role->permissions()->syncWithoutDetaching([$permission->id]));

        $this->command?->info('manage_promotions permission ready.');
    }
}
