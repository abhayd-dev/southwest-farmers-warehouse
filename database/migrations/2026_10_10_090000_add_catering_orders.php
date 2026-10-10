<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catering (kitchen spec section 8, contract B.7).
 * - store_settings: catering on/off, standard notice (default 7 calendar
 *   days) and the daily cutoff time (orders after it count from the next day).
 * - catering_urgency_tiers: per store, "orders with N..M days' notice" ->
 *   surcharge X% or block. Inside the standard notice with no matching tier
 *   = blocked. The client sets the real tiers (product owner decision).
 * - sales: catering fields; a catering order is order_type 'Catering' with
 *   due_at = requested date/time.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('store_settings', 'catering_enabled')) {
                    $table->boolean('catering_enabled')->default(true);
                }
                if (!Schema::hasColumn('store_settings', 'catering_notice_days')) {
                    $table->unsignedSmallInteger('catering_notice_days')->default(7);
                }
                if (!Schema::hasColumn('store_settings', 'catering_cutoff_time')) {
                    $table->string('catering_cutoff_time', 5)->default('17:00'); // HH:MM store local time
                }
            });
        }

        if (!Schema::hasTable('catering_urgency_tiers')) {
            Schema::create('catering_urgency_tiers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->index();
                $table->unsignedSmallInteger('min_days'); // inclusive
                $table->unsignedSmallInteger('max_days'); // inclusive
                $table->string('action', 10)->default('surcharge'); // surcharge | block
                $table->decimal('percent', 5, 2)->default(0);
                $table->timestamps();
            });
        }

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'catering_notice_days')) {
                $table->smallInteger('catering_notice_days')->nullable();
                $table->decimal('catering_surcharge_percent', 5, 2)->nullable();
                $table->decimal('catering_surcharge_amount', 12, 2)->nullable();
                $table->string('contact_name')->nullable();
                $table->string('contact_phone', 30)->nullable();
                $table->unsignedInteger('guest_count')->nullable();
            }
            if (!Schema::hasColumn('sales', 'special_instructions')) {
                $table->text('special_instructions')->nullable();
            }
            if (!Schema::hasColumn('sales', 'order_type')) {
                $table->string('order_type')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            foreach (['catering_notice_days', 'catering_surcharge_percent', 'catering_surcharge_amount', 'contact_name', 'contact_phone', 'guest_count'] as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('catering_urgency_tiers');

        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                foreach (['catering_enabled', 'catering_notice_days', 'catering_cutoff_time'] as $col) {
                    if (Schema::hasColumn('store_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
