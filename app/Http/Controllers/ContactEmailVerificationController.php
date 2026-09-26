<?php

namespace App\Http\Controllers;

use App\Services\EmailVerificationService;
use Illuminate\Http\Request;

class ContactEmailVerificationController extends Controller
{
    public function verify(Request $request, string $type, int $id, EmailVerificationService $service)
    {
        $email = $request->query('email', '');

        try {
            $success = $service->confirm($type, $id, $email);
        } catch (\InvalidArgumentException $e) {
            $success = false;
        }

        return view('emails.verify-contact-email-result', [
            'success' => $success,
        ]);
    }

    /**
     * Manually resend a verification link for staff/vendor/PO-approval email,
     * triggered from the warehouse admin UI. Requires an authenticated staff session.
     */
    public function resend(Request $request, EmailVerificationService $service)
    {
        $request->validate([
            'type' => 'required|string|in:staff,vendor,po_approval',
            'id' => 'required|integer',
            'label' => 'required|string',
        ]);

        try {
            $config = EmailVerificationService::registry($request->type);
            $model = $config['model']::findOrFail($request->id);
        } catch (\Throwable $e) {
            return back()->with('error', \App\Support\ErrorMessage::from($e, 'Could not find the record to verify.'));
        }

        $sent = $service->send($request->type, $model, $request->label);

        // Show the real reason: this used to say "check that an email address
        // is on file" for every failure, including the mail server refusing
        // the message (client PDF 9/24, items 5-6).
        return back()->with($sent ? 'success' : 'error', $sent
            ? 'Verification email sent.'
            : 'Could not send verification email to ' . ($model->{$config['email_field']} ?: 'this record') . ': ' . $service->lastError);
    }
}
