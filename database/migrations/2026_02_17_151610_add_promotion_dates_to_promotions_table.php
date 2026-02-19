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
        Schema::table('promotions', function (Blueprint $table) {
            if (!Schema::hasColumn('promotions', 'start_date')) {
                $table->date('start_date')->nullable()->after('discount_percent');
            }
            if (!Schema::hasColumn('promotions', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('promotions', 'auto_revert')) {
                $table->boolean('auto_revert')->default(true)->after('end_date');
            }
            if (!Schema::hasColumn('promotions', 'original_price')) {
                $table->decimal('original_price', 15, 2)->nullable()->after('auto_revert');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            //
        });
    }
};
