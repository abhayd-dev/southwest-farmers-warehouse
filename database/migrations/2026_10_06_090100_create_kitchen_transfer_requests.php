<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kitchen spec 7.1 / 7.3 (contract B.5, B.6): a General Manager requests
 * store stock for the store's kitchen; it stays pending until an Area
 * Manager approves (stock then moves) or denies. The requester can never
 * decide their own request.
 *
 * Who is "GM" / "Area Manager" is a client decision, so they are two
 * permissions that can be ticked on any store role in Access Control:
 *   kitchen_transfer_request, kitchen_transfer_approve.
 * Both are given to the store "Super Admin" role so someone can use it now.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kitchen_transfer_requests')) {
            Schema::create('kitchen_transfer_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->foreignId('kitchen_location_id')->constrained('kitchen_locations')->cascadeOnDelete();
                $table->string('status', 20)->default('pending'); // pending, approved, denied, cancelled
                $table->text('notes')->nullable();                 // requester's reason / notes
                $table->unsignedBigInteger('requested_by')->nullable();
                $table->string('requested_by_name')->nullable();
                $table->unsignedBigInteger('decided_by')->nullable();
                $table->string('decided_by_name')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->text('decision_note')->nullable();          // deny reason / approver note
                $table->timestamps();
                $table->index(['store_id', 'status', 'created_at']);
            });
        }

        if (!Schema::hasTable('kitchen_transfer_request_items')) {
            Schema::create('kitchen_transfer_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kitchen_transfer_request_id')->constrained('kitchen_transfer_requests')->cascadeOnDelete();
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 12, 2);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('store_permissions')) {
            $guard = DB::table('store_permissions')->value('guard_name') ?? 'store_user';
            $now = now();
            foreach (['kitchen_transfer_request', 'kitchen_transfer_approve'] as $name) {
                if (!DB::table('store_permissions')->where('name', $name)->exists()) {
                    DB::table('store_permissions')->insert([
                        'name' => $name, 'guard_name' => $guard, 'group_name' => 'Kitchen',
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }

            $superAdmin = DB::table('store_roles')->where('name', 'Super Admin')->value('id');
            if ($superAdmin && Schema::hasTable('store_role_has_permissions')) {
                foreach (DB::table('store_permissions')->whereIn('name', ['kitchen_transfer_request', 'kitchen_transfer_approve'])->pluck('id') as $pid) {
                    $exists = DB::table('store_role_has_permissions')->where(['permission_id' => $pid, 'role_id' => $superAdmin])->exists();
                    if (!$exists) {
                        DB::table('store_role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $superAdmin]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_transfer_request_items');
        Schema::dropIfExists('kitchen_transfer_requests');

        if (Schema::hasTable('store_permissions')) {
            $ids = DB::table('store_permissions')->whereIn('name', ['kitchen_transfer_request', 'kitchen_transfer_approve'])->pluck('id');
            if (Schema::hasTable('store_role_has_permissions')) {
                DB::table('store_role_has_permissions')->whereIn('permission_id', $ids)->delete();
            }
            DB::table('store_permissions')->whereIn('id', $ids)->delete();
        }
    }
};
