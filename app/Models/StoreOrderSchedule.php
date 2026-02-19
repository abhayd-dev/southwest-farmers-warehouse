<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Existing table schema (from 2026_02_17_151548):
 * - store_id, expected_day (string: Monday...), time_window_start, time_window_end,
 *   cutoff_time, notification_recipients (json), is_active
 */
class StoreOrderSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'expected_day',
        'time_window_start',
        'time_window_end',
        'cutoff_time',
        'notification_recipients',
        'is_active',
    ];

    protected $casts = [
        'is_active'                => 'boolean',
        'notification_recipients'  => 'array',
    ];

    public const DAYS = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',
    ];

    // ===== RELATIONSHIPS =====

    public function store()
    {
        return $this->belongsTo(StoreDetail::class, 'store_id');
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Schedules due today (matching today's day name)
     */
    public function scopeDueToday($query)
    {
        return $query->active()->where('expected_day', now()->format('l'));
    }
}
