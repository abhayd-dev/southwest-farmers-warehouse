<?php

namespace App\Console\Commands;

use App\Mail\ApproverPOConfirmation;
use App\Mail\ApproverPORejected;
use App\Mail\IncomingStoreOrderNotification;
use App\Mail\LateDeliveryAlert;
use App\Mail\LowStockAlert;
use App\Mail\OrderLateAlert;
use App\Mail\OverReceiptApprovalRequest;
use App\Mail\PODelayedMail;
use App\Mail\ShortageReportNotification;
use App\Mail\StoreOrderCreated;
use App\Mail\SupportTicketCreated;
use App\Mail\SupportTicketReplied;
use App\Mail\SupportTicketStatusChanged;
use App\Mail\VerifyContactEmail;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StorePurchaseOrder;
use App\Models\SupportTicket;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * php artisan mail:send-samples someone@example.com
 *
 * Sends ONE of every email this app sends, all to the given address, using
 * existing records read-only (nothing is created, updated or approved).
 * Buttons that would approve / reject / cancel point to a harmless page
 * instead. Prints "sent" or the mail server's exact reason per email.
 */
class MailSendSamples extends Command
{
    protected $signature = 'mail:send-samples {to : Address every sample email goes to} {--only= : Run just one sample by its number}';

    protected $description = 'Send one sample of every warehouse email to one address (read-only)';

    public function handle(): int
    {
        $to = $this->argument('to');
        $safeUrl = url('/dashboard'); // harmless target for action buttons in samples

        $po = PurchaseOrder::with(['vendor', 'items.product'])->whereHas('items')->latest('id')->first();
        $storePo = StorePurchaseOrder::with(['store', 'items.product'])->latest('id')->first();
        $ticket = SupportTicket::with('messages')->whereHas('messages')->latest('id')->first();
        $products = Product::whereNull('store_id')->where('is_active', true)->with('stock')->limit(3)->get();

        $samples = [
            // An unsaved user just carries the address: nothing is written.
            'Password reset' => fn () => (new \App\Models\WareUser(['name' => 'Sample', 'email' => $to]))->notify(new ResetPassword('sample-token-not-valid')),
            'Contact email verification' => fn () => Mail::to($to)->send(new VerifyContactEmail('Purchase Order approvals (sample)', $to, $safeUrl)),
            'PO sent for approval' => fn () => $this->requirePo($po) ?? Mail::send('emails.purchase-order-approval', ['po' => $po, 'approveUrl' => $safeUrl, 'rejectUrl' => $safeUrl, 'isReminder' => false],
                fn ($m) => $m->to($to)->subject("Purchase Order #{$po->po_number} - Approval Required")),
            'PO approval reminder' => fn () => $this->requirePo($po) ?? Mail::send('emails.purchase-order-approval', ['po' => $po, 'approveUrl' => $safeUrl, 'rejectUrl' => $safeUrl, 'isReminder' => true],
                fn ($m) => $m->to($to)->subject("Reminder: Purchase Order #{$po->po_number} - Approval Still Needed")),
            'Approver: PO approved confirmation' => fn () => $this->requirePo($po) ?? Mail::to($to)->send(new ApproverPOConfirmation($po, $safeUrl)),
            'Approver: PO rejected confirmation' => fn () => $this->requirePo($po) ?? Mail::to($to)->send(new ApproverPORejected($po)),
            'Purchase order to vendor' => fn () => $this->requirePo($po) ?? Mail::send('emails.vendor-purchase-order', ['po' => $po, 'acknowledgeUrl' => $safeUrl, 'denyUrl' => $safeUrl],
                fn ($m) => $m->to($to)->subject("Purchase Order #{$po->po_number} from Southwest Farmers Warehouse")),
            'Over-receipt approval request' => fn () => $this->requirePo($po) ?? Mail::to($to)->send(new OverReceiptApprovalRequest($po, $safeUrl, $safeUrl)),
            'Shortage report' => fn () => $this->requirePo($po) ?? Mail::to($to)->send(new ShortageReportNotification($po, $po->items->take(2))),
            'PO delayed alert' => fn () => $this->requirePo($po) ?? Mail::to($to)->send(new PODelayedMail($po, 1)),
            'Late delivery alert (daily)' => fn () => $this->requirePo($po) ?? Mail::to($to)->send(new LateDeliveryAlert(collect([$po]))),
            'Low stock alert (daily)' => fn () => $products->isEmpty() ? 'SKIPPED: no products' : Mail::to($to)->send(new LowStockAlert($products)),
            'Store order created' => fn () => $storePo ? Mail::to($to)->send(new StoreOrderCreated($storePo)) : 'SKIPPED: no store order yet',
            'Incoming store order (warehouse manager)' => fn () => $storePo ? Mail::to($to)->send(new IncomingStoreOrderNotification($storePo)) : 'SKIPPED: no store order yet',
            'Store order late alert' => fn () => $storePo ? Mail::to($to)->send(new OrderLateAlert($storePo)) : 'SKIPPED: no store order yet',
            'Support ticket created' => fn () => $ticket ? Mail::to($to)->send(new SupportTicketCreated($ticket)) : 'SKIPPED: no ticket with messages',
            'Support ticket replied' => fn () => $ticket ? Mail::to($to)->send(new SupportTicketReplied($ticket, $ticket->messages->last())) : 'SKIPPED: no ticket with messages',
            'Support ticket status changed' => fn () => $ticket ? Mail::to($to)->send(new SupportTicketStatusChanged($ticket)) : 'SKIPPED: no ticket with messages',
            'Plain test email (mail:test)' => fn () => Mail::raw('Plain test email from ' . config('app.name') . ' at ' . now()->toDateTimeString() . ' UTC.',
                fn ($m) => $m->to($to)->subject('Mail test - ' . config('app.name'))),
        ];

        $this->info('Mailer: ' . config('mail.default') . ' via ' . config('mail.mailers.' . config('mail.default') . '.host', '-') . '   From: ' . config('mail.from.address'));
        $rows = []; $failed = 0; $n = 0;
        foreach ($samples as $name => $send) {
            $n++;
            if ($this->option('only') && (int) $this->option('only') !== $n) {
                continue;
            }
            try {
                $result = $send();
                $status = is_string($result) && str_starts_with($result, 'SKIPPED') ? $result : 'sent';
            } catch (\Throwable $e) {
                $failed++;
                $status = 'FAILED: ' . \App\Support\MailFailure::reason($e);
            }
            $rows[] = [$n, $name, $status];
        }
        $this->table(['#', 'Email', 'Result'], $rows);
        $this->line("All sent to {$to}. Samples use PO #" . ($po->po_number ?? '-') . ', store order #' . ($storePo->po_number ?? $storePo->id ?? '-') . ', ticket #' . ($ticket->ticket_number ?? '-') . '.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function requirePo(?PurchaseOrder $po): ?string
    {
        return $po ? null : 'SKIPPED: no purchase order with items';
    }
}
