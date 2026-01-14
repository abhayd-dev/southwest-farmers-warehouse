<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Products Table (Add Conversion & Batch Flags)
        Schema::table('products', function (Blueprint $table) {
            $table->string('purchase_unit')->nullable()->after('unit')->comment('e.g., Box, Carton');
            $table->integer('conversion_factor')->default(1)->after('purchase_unit')->comment('How many Base Units in one Purchase Unit');
            $table->boolean('is_batch_active')->default(false)->after('conversion_factor')->comment('Track Expiry/Batch for this product?');
        });

        // 2. Product Batches (FIFO Logic)
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('ware_details')->cascadeOnDelete();
            
            $table->string('batch_number')->index();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable()->index(); // Indexed for fast FIFO sorting
            
            $table->decimal('cost_price', 12, 2)->comment('Purchase price per Base Unit');
            $table->decimal('quantity', 12, 2)->default(0)->comment('Remaining Quantity in Base Units');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Stock Transactions (Audit Trail)
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('ware_details')->cascadeOnDelete();
            
            // Nullable because some actions (like global adjustment) might not target a specific batch immediately,
            // though for FIFO we will try to link every move to a batch.
            $table->foreignId('product_batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            
            // Who performed the action?
            // Assuming you use 'ware_users' or standard 'users'. 
            // Based on your uploaded files, you use 'WareUser'. Adjust if using generic User.
            $table->unsignedBigInteger('user_id')->nullable(); 
            
            $table->enum('type', [
                'purchase',      // Inward
                'sale',          // Outward
                'return',        // Inward
                'damage',        // Outward
                'adjustment',    // +/-
                'transfer_in',   // Inward
                'transfer_out'   // Outward
            ])->index();

            $table->decimal('quantity_change', 12, 2)->comment('Negative for Outward, Positive for Inward');
            $table->decimal('running_balance', 12, 2)->comment('Total Product Stock after this move');
            
            $table->string('reference_id')->nullable()->comment('PO Number, Order ID, etc.');
            $table->text('remarks')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('product_batches');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['purchase_unit', 'conversion_factor', 'is_batch_active']);
        });
    }
};