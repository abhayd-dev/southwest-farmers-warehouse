<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Items sold by weight take their quantity from the POS scale (e.g. 1.25 lb).
 * store_stocks and product_batches already hold decimals, but cart_items and
 * sale_items were integer, so Postgres rejected the weighed quantity and the
 * sale failed. Same NUMERIC(15,2) as store_stocks. Only touches a column that
 * is still an integer, so it is safe to run where it was already changed.
 */
return new class extends Migration
{
    private array $columns = [['cart_items', 'quantity'], ['sale_items', 'quantity']];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return; // SQLite does not enforce column types
        }

        foreach ($this->columns as [$table, $column]) {
            if (Schema::hasTable($table) && in_array(Schema::getColumnType($table, $column), ['integer', 'int4', 'bigint', 'int8', 'smallint', 'int2'], true)) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE NUMERIC(15,2) USING {$column}::numeric");
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->columns as [$table, $column]) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE INTEGER USING ROUND({$column})::integer");
            }
        }
    }
};
