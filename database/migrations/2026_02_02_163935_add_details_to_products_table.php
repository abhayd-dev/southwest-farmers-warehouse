<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Foreign Key for Department
            $table->foreignId('department_id')->nullable()->after('subcategory_id')->constrained('departments')->nullOnDelete();
            
            // New Item Master Fields
            $table->string('pack_size')->nullable()->after('unit'); // e.g., "12 x 1 lb"
            $table->decimal('box_weight', 8, 2)->nullable()->after('pack_size'); // For pallet building
            $table->integer('shelf_life_days')->nullable()->after('box_weight'); // For expiry calculation
            $table->boolean('taxable')->default(true)->after('tax_percent'); 
            $table->decimal('margin_percent', 5, 2)->nullable()->after('price'); // Retail vs Cost margin
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'pack_size', 'box_weight', 'shelf_life_days', 'taxable', 'margin_percent']);
        });
    }
};