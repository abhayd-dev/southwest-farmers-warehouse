<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Store Groups (client PDF 9/22, Store item 6): stores are grouped on the
 * Warehouse side (Stores -> All Stores), and a staff member such as a
 * Regional Manager can be assigned a group on the Store side, which lets
 * them switch between every store in that group.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('store_groups')) {
            Schema::create('store_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('store_details', 'store_group_id')) {
            Schema::table('store_details', function (Blueprint $table) {
                $table->foreignId('store_group_id')->nullable()->constrained('store_groups')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('store_users', 'store_group_id')) {
            Schema::table('store_users', function (Blueprint $table) {
                $table->foreignId('store_group_id')->nullable()->constrained('store_groups')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['store_users', 'store_details'] as $table) {
            if (Schema::hasColumn($table, 'store_group_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('store_group_id');
                });
            }
        }
        Schema::dropIfExists('store_groups');
    }
};
