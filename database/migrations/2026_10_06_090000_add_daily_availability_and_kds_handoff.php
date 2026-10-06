<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kitchen spec phase 1 step 2 (contract B.2 daily availability, B.3 kitchen
 * order flow).
 *
 * - menu_categories / menu_items: store_id, image, deleted_at existed only in
 *   a store-repo migration (2026_09_13_090126), which never runs on deploy.
 *   Same guarded definitions here.
 * - menu_items: availability_status (available / unavailable / sold_out),
 *   the day it was marked sold out, who and when; backfilled from
 *   is_available_today. menu_item_availability_logs keeps every change.
 * - store_settings: how the website treats Sold Out items, and whether Sold
 *   Out clears by itself the next day (client decisions; defaults below).
 * - sales: KDS 'Handoff' step, cancel reason, who/when of the last status
 *   change, and an optional due time (catering will set it).
 */
return new class extends Migration
{
    private const KDS_STATUSES = ['New', 'Accepted', 'Preparing', 'Ready', 'Handoff', 'Completed', 'Cancelled'];

    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_categories', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('menu_categories', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('menu_categories', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('menu_items', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('menu_items', 'availability_status')) {
                $table->string('availability_status', 20)->default('available');
                $table->date('sold_out_on')->nullable();
                $table->timestamp('availability_changed_at')->nullable();
                $table->string('availability_changed_by')->nullable();
            }
        });

        if (Schema::hasColumn('menu_items', 'is_available_today')) {
            DB::table('menu_items')->where('is_available_today', false)->update(['availability_status' => 'unavailable']);
        }

        if (!Schema::hasTable('menu_item_availability_logs')) {
            Schema::create('menu_item_availability_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->string('from_status', 20)->nullable();
                $table->string('to_status', 20);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['store_id', 'created_at']);
            });
        }

        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('store_settings', 'sold_out_display')) {
                    $table->string('sold_out_display', 10)->default('show'); // show | hide
                }
                if (!Schema::hasColumn('store_settings', 'sold_out_resets_daily')) {
                    $table->boolean('sold_out_resets_daily')->default(true);
                }
            });
        }

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'kitchen_cancel_reason')) {
                $table->string('kitchen_cancel_reason')->nullable();
            }
            if (!Schema::hasColumn('sales', 'kitchen_status_changed_at')) {
                $table->timestamp('kitchen_status_changed_at')->nullable();
                $table->string('kitchen_status_changed_by')->nullable();
            }
            if (!Schema::hasColumn('sales', 'due_at')) {
                $table->timestamp('due_at')->nullable();
            }
        });

        // kitchen_status was created as an enum: on Postgres that is a CHECK
        // constraint, which must list the new 'Handoff' step.
        if (DB::connection()->getDriverName() === 'pgsql' && Schema::hasColumn('sales', 'kitchen_status')) {
            DB::statement('ALTER TABLE sales DROP CONSTRAINT IF EXISTS sales_kitchen_status_check');
            DB::statement("ALTER TABLE sales ADD CONSTRAINT sales_kitchen_status_check CHECK (kitchen_status IN ('" . implode("','", self::KDS_STATUSES) . "'))");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql' && Schema::hasColumn('sales', 'kitchen_status')) {
            DB::table('sales')->where('kitchen_status', 'Handoff')->update(['kitchen_status' => 'Completed']);
            DB::statement('ALTER TABLE sales DROP CONSTRAINT IF EXISTS sales_kitchen_status_check');
            DB::statement("ALTER TABLE sales ADD CONSTRAINT sales_kitchen_status_check CHECK (kitchen_status IN ('New','Accepted','Preparing','Ready','Completed','Cancelled'))");
        }

        Schema::table('sales', function (Blueprint $table) {
            foreach (['kitchen_cancel_reason', 'kitchen_status_changed_at', 'kitchen_status_changed_by', 'due_at'] as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                foreach (['sold_out_display', 'sold_out_resets_daily'] as $col) {
                    if (Schema::hasColumn('store_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('menu_item_availability_logs');

        Schema::table('menu_items', function (Blueprint $table) {
            foreach (['availability_status', 'sold_out_on', 'availability_changed_at', 'availability_changed_by'] as $col) {
                if (Schema::hasColumn('menu_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        // store_id / image / deleted_at stay: the store app relies on them.
    }
};
