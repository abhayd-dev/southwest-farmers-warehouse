<?php

namespace App\Support;

/**
 * Turns a mail transport exception into a short, safe reason a user can act
 * on (e.g. "550 5.7.1 Relaying denied" or "You can only send testing emails
 * to your own email address"), instead of a generic "email failed".
 */
class MailFailure
{
    public static function reason(\Throwable $e): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $e->getMessage()));

        // Symfony SMTP errors read: Expected response code "250" but got code "550", with message "550 5.7.1 ...".
        if (preg_match('/with message "([^"]+)"/', $message, $m)) {
            $message = $m[1];
        }

        // Never echo credentials that some transports include in DSNs.
        $message = preg_replace('#(smtps?|resend|postmark|ses|mailgun)://[^@\s]+@#i', '$1://***@', $message);

        return mb_strimwidth($message, 0, 220, '…');
    }
}
