<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportTask extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'status',
        'total_rows',
        'processed_rows',
        'file_name',
        'status_message',
        'error_message',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function user()
    {
        return $this->belongsTo(WareUser::class, 'user_id');
    }

    public function getPercentageAttribute()
    {
        if ($this->total_rows <= 0) {
            return 0;
        }

        return min(100, round(($this->processed_rows / $this->total_rows) * 100));
    }
}
