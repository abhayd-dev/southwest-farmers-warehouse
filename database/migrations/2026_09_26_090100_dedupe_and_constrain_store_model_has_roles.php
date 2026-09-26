<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * store_model_has_roles had no unique key, so sync() (or a double form
 * submission racing two sync() calls) could leave a staff member with the
 * same role attached twice — the client saw "Super Admin, Super Admin" /
 * "Sales Staff, Sales Staff" badges on the Staff screen. Removes exact
 * duplicate rows (keeping the lowest ctid of each group) and adds a unique
 * index so a duplicate row can never exist again.
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
        if (Schema::hasTable('store_model_has_roles')) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement("
                    DELETE FROM store_model_has_roles a USING store_model_has_roles b
                    WHERE a.ctid > b.ctid
                      AND a.model_id = b.model_id
                      AND a.model_type = b.model_type
                      AND a.role_id = b.role_id
                ");
            } else {
                DB::statement('
                    DELETE FROM store_model_has_roles
                    WHERE rowid NOT IN (
                        SELECT MIN(rowid) FROM store_model_has_roles
                        GROUP BY model_id, model_type, role_id
                    )
                ');
            }

            if (!Schema::hasIndex('store_model_has_roles', 'store_model_has_roles_unique')) {
                Schema::table('store_model_has_roles', function (Blueprint $table) {
                    $table->unique(['model_id', 'model_type', 'role_id'], 'store_model_has_roles_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('store_model_has_roles') && Schema::hasIndex('store_model_has_roles', 'store_model_has_roles_unique')) {
            Schema::table('store_model_has_roles', function (Blueprint $table) {
                $table->dropUnique('store_model_has_roles_unique');
            });
        }
    }
};
