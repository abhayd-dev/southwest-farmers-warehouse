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
        // Store Order Schedules
        Schema::create('store_order_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('store_details')->onDelete('cascade');
            $table->string('expected_day'); // Monday, Tuesday, etc.
            $table->time('time_window_start')->nullable();
            $table->time('time_window_end')->nullable();
            $table->time('cutoff_time');
            $table->json('notification_recipients')->nullable(); // Array of phone numbers/emails
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Notification Logs
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // sms, email
            $table->string('recipient');
            $table->text('message');
            $table->string('status')->default('sent'); // sent, failed, pending
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->string('notification_for')->nullable(); // late_order, low_stock, expiration, etc.
            $table->foreignId('related_id')->nullable(); // ID of related record (store_id, product_id, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('store_order_schedules');
    }
};
