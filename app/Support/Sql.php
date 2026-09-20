<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * SQL fragments whose syntax differs by database. Production is Postgres; the
 * test suite runs on SQLite, so anything date-formatted goes through here.
 */
class Sql
{
    /** "YYYY-MM-DD" text for a timestamp column. */
    public static function dayKey(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM-DD')",
            'sqlite' => "strftime('%Y-%m-%d', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }
}
