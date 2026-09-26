<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * php artisan mail:test someone@example.com
 *
 * Sends one plain test email with the app's real mail settings and prints
 * either "sent" or the exact error the mail server returned. Prints the
 * settings in use (never the password) so a misconfiguration is obvious.
 */
class MailTest extends Command
{
    protected $signature = 'mail:test {to : Address to send the test email to}';

    protected $description = 'Send a test email and show the exact mail-server response';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $cfg = config("mail.mailers.{$mailer}", []);

        $this->table(['Setting', 'Value'], [
            ['mailer', $mailer],
            ['host', $cfg['host'] ?? '-'],
            ['port', $cfg['port'] ?? '-'],
            ['encryption / scheme', $cfg['encryption'] ?? $cfg['scheme'] ?? '-'],
            ['username', $cfg['username'] ?? '-'],
            ['from address', config('mail.from.address')],
            ['from name', config('mail.from.name')],
        ]);

        try {
            Mail::raw('Test email from ' . config('app.name') . ' at ' . now()->toDateTimeString() . ' UTC.', function ($m) {
                $m->to($this->argument('to'))->subject('Mail test - ' . config('app.name'));
            });
        } catch (\Throwable $e) {
            $this->error('FAILED: ' . \App\Support\MailFailure::reason($e));
            $this->line(get_class($e));

            return self::FAILURE;
        }

        $this->info('Sent to ' . $this->argument('to') . ' (accepted by the mail server).');

        return self::SUCCESS;
    }
}
