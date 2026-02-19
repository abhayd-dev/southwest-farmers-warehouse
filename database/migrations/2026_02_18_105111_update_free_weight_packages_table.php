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
        Schema::table('free_weight_packages', function (Blueprint $table) {
            $table->foreignId('target_product_id')->nullable()->after('free_weight_product_id')->constrained('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('free_weight_packages', function (Blueprint $table) {
            $table->dropForeign(['target_product_id']);
            $table->dropColumn('target_product_id');
        });
    }
};
