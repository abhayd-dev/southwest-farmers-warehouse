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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('carton_length', 8, 2)->nullable()->after('weight');
            $table->decimal('carton_width', 8, 2)->nullable()->after('carton_length');
            $table->decimal('carton_height', 8, 2)->nullable()->after('carton_width');
            $table->integer('units_per_carton')->default(1)->after('carton_height');
            $table->boolean('is_stackable')->default(true)->after('units_per_carton');
            $table->boolean('is_fragile')->default(false)->after('is_stackable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'carton_length',
                'carton_width',
                'carton_height',
                'units_per_carton',
                'is_stackable',
                'is_fragile'
            ]);
        });
    }
};
