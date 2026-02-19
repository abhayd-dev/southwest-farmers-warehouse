<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'customer_id',
        'invoice_number',
        'subtotal',
        'gst_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_method',
        'created_by',
    ];

    public function store()
    {
        return $this->belongsTo(StoreDetail::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(StoreUser::class, 'created_by');
    }
}
