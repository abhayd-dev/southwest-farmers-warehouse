<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarded copy of the store-repo migration 2026_06_25_145715 (store-repo
 * migrations don't run on deploy): cart / sale / return lines can be a
 * kitchen menu item instead of a product. Live already has these columns,
 * so this only runs where they are missing (fresh / test databases).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['cart_items', 'sale_items', 'sale_return_items'] as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t, 'menu_item_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
                    $table->unsignedBigInteger('product_id')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        // Left in place: the store app depends on these columns.
    }
};
