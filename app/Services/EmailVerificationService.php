<?php

namespace App\Services;

use App\Mail\VerifyContactEmail;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\WareUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationService
{
    /**
     * Registry of verifiable contexts. Add an entry here to support
     * verification for a new email field elsewhere in the app.
     */
    protected static array $registry = [
        'staff' => [
            'model' => WareUser::class,
            'email_field' => 'email',
            'verified_field' => 'email_verified_at',
        ],
        'vendor' => [
            'model' => Vendor::class,
            'email_field' => 'email',
            'verified_field' => 'email_verified_at',
        ],
        'po_approval' => [
            'model' => PurchaseOrder::class,
            'email_field' => 'approval_email',
            'verified_field' => 'approval_email_verified_at',
        ],
    ];

    public static function registry(string $type): array
    {
        if (!isset(self::$registry[$type])) {
            throw new \InvalidArgumentException("Unknown email verification type [{$type}]");
        }

        return self::$registry[$type];
    }

    /**
     * Send (or resend) a verification email for the given model's email field.
     * Safe to call every time an email is saved — it always sends a fresh link.
     */
    public function send(string $type, $model, string $label): bool
    {
        $config = self::registry($type);
        $email = $model->{$config['email_field']};

        if (!$email) {
            return false;
        }

        $verifyUrl = URL::temporarySignedRoute(
            'contact-email.verify',
            now()->addDays(7),
            ['type' => $type, 'id' => $model->id, 'email' => $email]
        );

        try {
            Mail::to($email)->send(new VerifyContactEmail($label, $email, $verifyUrl));
        } catch (\Throwable $e) {
            Log::error("Failed to send verification email for {$type} #{$model->id}: " . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Called only when an email value actually changes (or is set for the first
     * time). Resets the verified flag and sends a fresh confirmation link.
     */
    public function handleEmailChange(string $type, $model, ?string $oldEmail, string $label): void
    {
        $config = self::registry($type);
        $newEmail = $model->{$config['email_field']};

        if ($newEmail === $oldEmail) {
            return; // unchanged — nothing to verify
        }

        // Reset verification on the new (unverified) address, then send a link.
        $model->{$config['verified_field']} = null;
        $model->saveQuietly();

        if ($newEmail) {
            $this->send($type, $model, $label);
        }
    }

    /**
     * Mark a model's email verified after a successful signed-link click.
     * Returns false if the address on file has since changed (stale link).
     */
    public function confirm(string $type, int $id, string $email): bool
    {
        $config = self::registry($type);
        $model = $config['model']::find($id);

        if (!$model || $model->{$config['email_field']} !== $email) {
            return false;
        }

        $model->{$config['verified_field']} = now();
        $model->saveQuietly();

        return true;
    }
}
