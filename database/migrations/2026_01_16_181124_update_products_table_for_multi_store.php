<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Nullable because Global products have NULL store_id
            $table->unsignedBigInteger('store_id')->nullable()->index()->after('id'); 
            
            // To soft delete local products without affecting others
            $table->softDeletes(); 
        });

        Schema::table('store_stocks', function (Blueprint $table) {
            // Ensure we track the selling price specific to this store
            // Assuming 'price' column exists, if not, add 'selling_price'
            if (!Schema::hasColumn('store_stocks', 'selling_price')) {
                $table->decimal('selling_price', 10, 2)->default(0)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'deleted_at']);
        });
        
        Schema::table('store_stocks', function (Blueprint $table) {
            $table->dropColumn('selling_price');
        });
    }
};