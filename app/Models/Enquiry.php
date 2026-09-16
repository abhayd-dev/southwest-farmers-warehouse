<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared `enquiries` table, owned by the Store app's public contact form.
 * This model exists here so Warehouse staff can see and act on enquiries
 * once a store has escalated them — Store -> Warehouse -> Main Super Admin.
 */
class Enquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
        'status',
        'escalated_at',
        'escalated_to_admin_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'escalated_at' => 'datetime',
        'escalated_to_admin_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    const STATUS_NEW = 'new';
    const STATUS_ESCALATED_WAREHOUSE = 'escalated_warehouse';
    const STATUS_ESCALATED_ADMIN = 'escalated_admin';
    const STATUS_RESOLVED = 'resolved';

    public function isEscalatedToWarehouse()
    {
        return $this->status === self::STATUS_ESCALATED_WAREHOUSE;
    }

    public function isEscalatedToAdmin()
    {
        return $this->status === self::STATUS_ESCALATED_ADMIN;
    }

    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }
}
