<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * A calendar day as warehouse staff mean it (config app.display_timezone),
 * turned into the UTC bounds timestamps are stored in. whereDate() alone
 * compares against the UTC date, which rolls over at 7pm Central.
 */
class DisplayDay
{
    public static function start(string $date): Carbon
    {
        return Carbon::parse($date, config('app.display_timezone'))->startOfDay()->utc();
    }

    public static function end(string $date): Carbon
    {
        return Carbon::parse($date, config('app.display_timezone'))->endOfDay()->utc();
    }
}
