<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'lead_time_days',
        'rating',
        'on_time_delivery_rate',
        'total_orders_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lead_time_days' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function updateRating()
    {
        $totalOrders = $this->purchaseOrders()
            ->where('status', 'completed')
            ->count();

        if ($totalOrders == 0) {
            $this->update(['rating' => 0, 'on_time_delivery_rate' => 0]);
            return;
        }

        $onTimeOrders = $this->purchaseOrders()
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->whereRaw('DATE(updated_at) <= expected_delivery_date')
                    ->orWhereNull('expected_delivery_date');
            })
            ->count();

        $percentage = ($onTimeOrders / $totalOrders) * 100;

        $stars = round(($percentage / 20), 1);

        $this->update([
            'rating' => $stars,
            'on_time_delivery_rate' => round($percentage, 1)
        ]);
    }
}
