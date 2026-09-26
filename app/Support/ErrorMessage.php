<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Shows warehouse staff the real reason an action failed instead of
 * "Something went wrong. Please try again later." (client request,
 * 2026-09-26: show real errors across the warehouse panel for now).
 *
 * Only for a logged-in warehouse user, and only while
 * config('app.show_real_errors') is on (SHOW_REAL_ERRORS, default on) --
 * set SHOW_REAL_ERRORS=false to go back to generic messages without a
 * code change. Public pages (vendor / approver email links) always get the
 * generic text. Credentials and connection details are stripped.
 */
class ErrorMessage
{
    public static function shouldShow(): bool
    {
        try {
            return (bool) config('app.show_real_errors') && Auth::guard('warehouse')->check();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * The message to show the user: the generic one, or the generic context
     * plus the real error when real errors are on.
     */
    public static function from(\Throwable $e, string $generic): string
    {
        // Business rules are written for people: show them as they are, always.
        if ($e instanceof \App\Exceptions\BusinessRuleException) {
            return $e->getMessage();
        }

        if (! static::shouldShow()) {
            return $generic;
        }

        // "Something went wrong. Please try again later." adds nothing next to the
        // real error; a specific message ("Failed to upload invoice document.") does.
        $context = preg_match('/something went wrong|please try again later/i', $generic) ? '' : rtrim($generic, ' .') . '. ';

        return $context . 'Error: ' . static::describe($e);
    }

    /**
     * Flash data for back()->with(...): a business rule becomes a yellow
     * 'warning' toast with just its message; anything else an 'error' toast
     * (with the real error while SHOW_REAL_ERRORS is on).
     */
    public static function flash(\Throwable $e, string $generic): array
    {
        return [$e instanceof \App\Exceptions\BusinessRuleException ? 'warning' : 'error' => static::from($e, $generic)];
    }

    /** "<message> (<ExceptionClass> at app/Path/File.php:123)" */
    public static function describe(\Throwable $e): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $e->getMessage())) ?: '(no message)';

        // Laravel's QueryException appends "(Connection: pgsql, Host: ..., Port: ..., Database: ..., SQL: ...)";
        // keep the SQL, drop the connection details.
        $message = preg_replace('/Connection: \w+, Host: [^,]+, Port: \d+, Database: [^,]+, SQL:/', 'SQL:', $message);
        // Never echo credentials that some transports include in DSNs.
        $message = preg_replace('#(\w+)://[^@\s/]+@#', '$1://***@', $message);
        $message = mb_strimwidth($message, 0, 600, '…');

        return $message . ' (' . class_basename($e) . ' at ' . static::where($e) . ')';
    }

    /** The first frame inside the app (not vendor/), as a relative path:line. */
    private static function where(\Throwable $e): string
    {
        $frames = array_merge([['file' => $e->getFile(), 'line' => $e->getLine()]], $e->getTrace());
        foreach ($frames as $frame) {
            $file = $frame['file'] ?? '';
            if ($file !== '' && ! str_contains($file, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
                return ltrim(str_replace(base_path(), '', $file), DIRECTORY_SEPARATOR) . ':' . ($frame['line'] ?? '?');
            }
        }

        return ltrim(str_replace(base_path(), '', $e->getFile()), DIRECTORY_SEPARATOR) . ':' . $e->getLine();
    }
}
