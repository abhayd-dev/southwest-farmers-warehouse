<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Existing table schema (from 2026_02_17_151548):
 * - type (string: sms/email), recipient, message, status (sent/failed/pending),
 *   error_message, sent_at, notification_for, related_id
 */
class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'recipient',
        'message',
        'status',
        'error_message',
        'sent_at',
        'notification_for',
        'related_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Notification types (channel)
    public const TYPE_SMS   = 'sms';
    public const TYPE_EMAIL = 'email';
    public const TYPE_IN_APP = 'in_app';

    // Notification for (purpose)
    public const FOR_STORE_PO_ALERT = 'store_po_alert';
    public const FOR_LATE_ORDER     = 'late_order';
    public const FOR_LOW_STOCK      = 'low_stock';
    public const FOR_AUTO_PO        = 'auto_po';

    // Status
    public const STATUS_SENT    = 'sent';
    public const STATUS_FAILED  = 'failed';
    public const STATUS_PENDING = 'pending';

    // ===== HELPERS =====

    /**
     * Quick log helper — adapted to existing schema
     */
    public static function record(
        string $notificationFor,
        string $message,
        string $type = self::TYPE_IN_APP,
        string $recipient = 'system',
        ?int $relatedId = null,
        string $status = self::STATUS_SENT
    ): self {
        return self::create([
            'type'             => $type,
            'recipient'        => $recipient,
            'message'          => $message,
            'status'           => $status,
            'sent_at'          => now(),
            'notification_for' => $notificationFor,
            'related_id'       => $relatedId,
        ]);
    }
}
