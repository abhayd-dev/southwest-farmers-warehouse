<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who may change kitchen stock (receive / use / adjust / waste / count /
 * levels) is a client decision (kitchen spec 12.1, Kitchen Lead). Its own
 * permission, given to every store role that already has adjust_stock so
 * nobody loses access today.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_permissions')) {
            return;
        }
        $guard = DB::table('store_permissions')->value('guard_name') ?? 'store_user';
        if (!DB::table('store_permissions')->where('name', 'kitchen_adjust_stock')->exists()) {
            DB::table('store_permissions')->insert(['name' => 'kitchen_adjust_stock', 'guard_name' => $guard, 'group_name' => 'Kitchen', 'created_at' => now(), 'updated_at' => now()]);
        }
        $new = DB::table('store_permissions')->where('name', 'kitchen_adjust_stock')->value('id');
        $old = DB::table('store_permissions')->where('name', 'adjust_stock')->value('id');
        if ($new && $old && Schema::hasTable('store_role_has_permissions')) {
            foreach (DB::table('store_role_has_permissions')->where('permission_id', $old)->pluck('role_id')->unique() as $roleId) {
                if (!DB::table('store_role_has_permissions')->where(['permission_id' => $new, 'role_id' => $roleId])->exists()) {
                    DB::table('store_role_has_permissions')->insert(['permission_id' => $new, 'role_id' => $roleId]);
                }
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('store_permissions')) {
            return;
        }
        $id = DB::table('store_permissions')->where('name', 'kitchen_adjust_stock')->value('id');
        if ($id) {
            DB::table('store_role_has_permissions')->where('permission_id', $id)->delete();
            DB::table('store_permissions')->where('id', $id)->delete();
        }
    }
};
