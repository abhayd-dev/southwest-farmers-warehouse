<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('is_catering_only')->default(false)->after('is_pre_cooked');
            $table->integer('advance_notice_days')->default(7)->after('is_catering_only');
            $table->decimal('rush_fee_percentage', 5, 2)->default(0)->after('advance_notice_days');
            $table->json('available_days')->nullable()->after('rush_fee_percentage');
            $table->boolean('is_available_today')->default(true)->after('available_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_catering_only',
                'advance_notice_days',
                'rush_fee_percentage',
                'available_days',
                'is_available_today',
            ]);
        });
    }
};
