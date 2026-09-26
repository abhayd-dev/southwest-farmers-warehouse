<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal profile photo for staff (client PDF 9/22, Store item 1). Kept
 * separate from store_users.profile, which the store-logo upload writes to.
 */
/*
 * Lives in warehouse-pos (not the store repo) because this repo created the
 * store_* tables and its Railway deploy runs `migrate --force`; the store
 * app's Docker start command does not, so store-repo migrations never ran.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('store_users', 'profile_photo')) {
            Schema::table('store_users', function (Blueprint $table) {
                $table->string('profile_photo')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('store_users', 'profile_photo')) {
            Schema::table('store_users', function (Blueprint $table) {
                $table->dropColumn('profile_photo');
            });
        }
    }
};
