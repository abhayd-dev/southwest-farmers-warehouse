<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `enquiries` is shared with the store app, which historically created it.
 * This repo's models read it too, so a fresh database built from this repo
 * alone (tests, staging) was missing it. Guarded, so it's a no-op wherever
 * the table already exists (production).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enquiries')) {
            return;
        }

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->string('status')->default('new');
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('escalated_to_admin_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
        });
    }

    public function down(): void
    {
        // Intentionally left empty: this table is shared with the store app.
    }
};
