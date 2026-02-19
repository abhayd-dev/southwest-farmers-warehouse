<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $table = 'price_history';

    protected $fillable = [
        'product_id',
        'old_price',
        'new_price',
        'old_margin',
        'new_margin',
        'changed_by',
        'changed_at',
        'effective_from',
        'effective_to',
        'reason',
        'change_type',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'old_margin' => 'decimal:2',
        'new_margin' => 'decimal:2',
        'changed_at' => 'datetime',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(WareUser::class, 'changed_by');
    }

    // Helper method to log price change
    public static function logChange($productId, $oldPrice, $newPrice, $userId, $reason = null, $changeType = 'manual')
    {
        return self::create([
            'product_id' => $productId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'changed_by' => $userId,
            'changed_at' => now(),
            'reason' => $reason,
            'change_type' => $changeType,
        ]);
    }
}
