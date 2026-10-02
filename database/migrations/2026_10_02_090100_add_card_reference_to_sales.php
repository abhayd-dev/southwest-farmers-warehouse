<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A PAX card sale returns a reference number (needed to void or refund it)
 * and an auth code (printed on the receipt), but the sale never stored them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'card_ref_num')) {
                $table->string('card_ref_num')->nullable();
            }
            if (! Schema::hasColumn('sales', 'card_auth_code')) {
                $table->string('card_auth_code')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            foreach (['card_ref_num', 'card_auth_code'] as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
