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
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('kitchen_status', ['New', 'Accepted', 'Preparing', 'Ready', 'Completed', 'Cancelled'])->default('New')->after('status');
            $table->string('order_type')->nullable()->after('kitchen_status')->comment('Pickup, Delivery, Dine-In');
            $table->text('special_instructions')->nullable()->after('order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['kitchen_status', 'order_type', 'special_instructions']);
        });
    }
};
