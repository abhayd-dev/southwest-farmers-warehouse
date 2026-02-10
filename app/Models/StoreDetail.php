<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class StoreDetail extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'store_details';

    protected $fillable = [
        'warehouse_id',
        'store_user_id',
        'store_name',
        'store_code',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * The warehouse this store belongs to.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * The manager (Store Super Admin) assigned to this store.
     */
    public function manager()
    {
        return $this->belongsTo(StoreUser::class, 'store_user_id');
    }

    /**
     * The stock/inventory items available at this store.
     */
    public function stocks()
    {
        return $this->hasMany(StoreStock::class, 'store_id');
    }

    /**
     * Stock requests for this store.
     */
    public function stockRequests()
    {
        return $this->hasMany(StockRequest::class, 'store_id');
    }

    /**
     * Recall requests involving this store.
     */
    public function recallRequests()
    {
        return $this->hasMany(RecallRequest::class, 'store_id');
    }

    /**
     * Store staff members.
     */
    public function staff()
    {
        return $this->hasMany(StoreUser::class, 'store_id');
    }

    /**
     * Product batches stored at this store.
     */
    public function batches()
    {
        return $this->hasMany(ProductBatch::class, 'store_id');
    }

    /**
     * Stock transactions for this store.
     */
    public function transactions()
    {
        return $this->hasMany(StockTransaction::class, 'store_id');
    }

    // ===== SCOPES =====

    /**
     * Scope to get only active stores.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get stores by city.
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to get stores by warehouse.
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // ===== METHODS =====

    /**
     * Get total inventory value for this store.
     */
    public function getTotalInventoryValue()
    {
        return $this->stocks()
            ->join('products', 'store_stocks.product_id', '=', 'products.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('store_stocks.quantity * products.cost_price'));
    }

    /**
     * Get total units in stock for this store.
     */
    public function getTotalStockUnits()
    {
        return $this->stocks()->sum('quantity');
    }

    /**
     * Get low stock items (below alert level).
     */
    public function getLowStockItems()
    {
        return $this->stocks()
            ->whereColumn('quantity', '<', 'alert_level')
            ->with('product')
            ->get();
    }

    /**
     * Get store location as array [latitude, longitude].
     */
    public function getLocation()
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'name' => $this->store_name,
            'city' => $this->city,
        ];
    }

    /**
     * Get store full address.
     */
    public function getFullAddress()
    {
        return trim("{$this->address}, {$this->city}, {$this->state} {$this->pincode}, {$this->country}");
    }

    /**
     * Check if store has manager assigned.
     */
    public function hasManager()
    {
        return $this->store_user_id !== null && $this->manager()->exists();
    }

    /**
     * Activate store.
     */
    public function activate()
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate store.
     */
    public function deactivate()
    {
        return $this->update(['is_active' => false]);
    }
}
