<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class VendorCommunicationService
{
    /**
     * Send PO to vendor via email
     */
    public function sendPOEmail(PurchaseOrder $po)
    {
        if (!$po->vendor->email) {
            throw new \Exception('Vendor does not have an email address');
        }

        Mail::send('emails.vendor-purchase-order', ['po' => $po], function ($message) use ($po) {
            $message->to($po->vendor->email, $po->vendor->name)
                    ->subject("Purchase Order #{$po->po_number} from Southwest Farmers Warehouse")
                    ->replyTo(config('app.warehouse_email', config('mail.from.address')));
        });

        // Log the communication
        Log::info("PO #{$po->po_number} sent to vendor {$po->vendor->name} via email", [
            'po_id' => $po->id,
            'vendor_email' => $po->vendor->email,
            'sent_at' => now(),
        ]);

        return true;
    }

    /**
     * Send PO to vendor via SMS (Twilio)
     */
    public function sendPOSMS(PurchaseOrder $po, $message = null)
    {
        if (!$po->vendor->phone) {
            throw new \Exception('Vendor does not have a phone number');
        }

        // Check if Twilio is configured
        if (!config('services.twilio.sid') || !config('services.twilio.token')) {
            throw new \Exception('Twilio is not configured. Please add TWILIO_SID and TWILIO_TOKEN to .env');
        }

        $defaultMessage = $message ?? "New Purchase Order #{$po->po_number} has been sent to you. Total: $" . number_format($po->total_amount, 2) . ". Please check your email for details.";

        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create(
                $po->vendor->phone,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $defaultMessage
                ]
            );

            // Log the communication
            Log::info("PO #{$po->po_number} SMS sent to vendor {$po->vendor->name}", [
                'po_id' => $po->id,
                'vendor_phone' => $po->vendor->phone,
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS for PO #{$po->po_number}: " . $e->getMessage());
            throw new \Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }

    /**
     * Send PO to vendor via both email and SMS
     */
    public function sendPOToVendor(PurchaseOrder $po, $includeEmail = true, $includeSMS = false)
    {
        $results = [
            'email' => false,
            'sms' => false,
            'errors' => [],
        ];

        if ($includeEmail) {
            try {
                $this->sendPOEmail($po);
                $results['email'] = true;
            } catch (\Exception $e) {
                $results['errors'][] = 'Email: ' . $e->getMessage();
            }
        }

        if ($includeSMS) {
            try {
                $this->sendPOSMS($po);
                $results['sms'] = true;
            } catch (\Exception $e) {
                $results['errors'][] = 'SMS: ' . $e->getMessage();
            }
        }

        return $results;
    }
}
