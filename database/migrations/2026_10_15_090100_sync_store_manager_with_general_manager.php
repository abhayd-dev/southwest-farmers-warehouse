<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ticket 20, one-time: where a store has exactly one active "General Manager"
 * on the store side and the warehouse shows someone else as its manager,
 * point the warehouse at that General Manager. Stores with no GM, or more
 * than one, are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_roles') || !Schema::hasTable('store_users') || !Schema::hasTable('store_model_has_roles')) {
            return;
        }

        $gmRoleIds = DB::table('store_roles')->where('name', 'General Manager')->pluck('id');
        if ($gmRoleIds->isEmpty()) {
            return;
        }

        $pivotGms = DB::table('store_model_has_roles')
            ->where('model_type', 'App\\Models\\StoreUser')
            ->whereIn('role_id', $gmRoleIds)
            ->pluck('model_id');

        $query = DB::table('store_users')
            ->where('is_active', true)
            ->whereNotNull('store_id')
            ->where(fn ($q) => $q->whereIn('store_role_id', $gmRoleIds)->orWhereIn('id', $pivotGms));
        if (Schema::hasColumn('store_users', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        foreach ($query->get(['id', 'store_id'])->groupBy('store_id') as $storeId => $gms) {
            if ($gms->count() !== 1) {
                continue;
            }
            DB::table('store_details')
                ->where('id', $storeId)
                ->where(fn ($q) => $q->whereNull('store_user_id')->orWhere('store_user_id', '!=', $gms->first()->id))
                ->update(['store_user_id' => $gms->first()->id, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Data fix only.
    }
};
