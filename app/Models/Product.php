<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;

    protected $casts = [
        'store_id' => 'integer',
        'category_id' => 'integer',
        'subcategory_id' => 'integer',
        'allow_decimal' => 'boolean',
        'is_active' => 'boolean',
        'promotion_start_date' => 'datetime',
        'promotion_end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $fillable = [
        'ware_user_id',
        'product_option_id',
        'category_id',
        'subcategory_id',
        'product_name',
        'sku',
        'upc',
        'barcode',
        'unit',
        'purchase_unit',
        'conversion_factor',
        'is_batch_active',
        'tax_percent',
        'price',
        'cost_price',
        'promotion_price',
        'promotion_start_date',
        'promotion_end_date',
        'icon',
        'department_id',
        'pack_size',
        'box_weight',
        'shelf_life_days',
        'taxable',
        'margin_percent',
        'is_active',
        'store_id',
        'carton_length',
        'carton_width',
        'carton_height',
        'units_per_carton',
        'is_stackable',
        'is_fragile',
    ];

    // ===== RELATIONSHIPS =====

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(ProductSubcategory::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class)->where('quantity', '>', 0)->orderBy('expiry_date', 'asc');
    }

    public function stock()
    {
        return $this->hasOne(ProductStock::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function storeStocks()
    {
        return $this->hasMany(StoreStock::class);
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class)->latest();
    }

    public function user()
    {
        return $this->belongsTo(WareUser::class, 'ware_user_id');
    }

    public function recallRequests()
    {
        return $this->hasMany(RecallRequest::class);
    }

    public function stockRequests()
    {
        return $this->hasMany(StockRequest::class);
    }

    public function marketPrices()
    {
        return $this->hasMany(ProductMarketPrice::class);
    }

    // ===== SCOPES =====

    /**
     * Get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get only warehouse products (not store-specific).
     */
    public function scopeWarehouse($query)
    {
        return $query->whereNull('store_id');
    }

    /**
     * Get products by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Get products by subcategory.
     */
    public function scopeBySubcategory($query, $subcategoryId)
    {
        return $query->where('subcategory_id', $subcategoryId);
    }

    /**
     * Get low stock products (warehouse level).
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->addSelect([
            'warehouse_qty' => ProductStock::selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('product_id', 'products.id')
                ->where('warehouse_id', 1)
        ])->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM product_stocks WHERE product_stocks.product_id = products.id AND product_stocks.warehouse_id = 1) < ?', [$threshold]);
    }

    // ===== STOCK MANAGEMENT METHODS =====

    /**
     * Add stock to warehouse with batch tracking.
     */
    public function addStock($warehouseId, $qty, $type, $batchData = [], $userId = null, $remarks = null)
    {
        return DB::transaction(function () use ($warehouseId, $qty, $type, $batchData, $userId, $remarks) {

            // 1. Create/Find Batch
            $batch = ProductBatch::create([
                'product_id' => $this->id,
                'warehouse_id' => $warehouseId,
                'batch_number' => $batchData['batch_number'] ?? 'GEN-' . time(),
                'manufacturing_date' => $batchData['mfg_date'] ?? null,
                'expiry_date' => $batchData['exp_date'] ?? null,
                'cost_price' => $batchData['cost_price'] ?? ($this->cost_price ?? 0),
                'quantity' => $qty,
                'is_active' => true,
            ]);

            // 2. Update Total Snapshot
            $stock = ProductStock::firstOrCreate(
                ['product_id' => $this->id, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $qty);

            // 3. Log Transaction
            StockTransaction::create([
                'product_id' => $this->id,
                'warehouse_id' => $warehouseId,
                'product_batch_id' => $batch->id,
                'ware_user_id' => $userId,
                'type' => $type,
                'quantity_change' => $qty,
                'running_balance' => $stock->quantity,
                'remarks' => $remarks
            ]);

            return $batch;
        });
    }

    /**
     * Remove stock from warehouse using FIFO.
     */
    public function removeStock($warehouseId, $qty, $type, $userId = null, $remarks = null)
    {
        return DB::transaction(function () use ($warehouseId, $qty, $type, $userId, $remarks) {

            $stock = ProductStock::where('product_id', $this->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (!$stock || $stock->quantity < $qty) {
                throw new \Exception("Insufficient Stock. Available: " . ($stock->quantity ?? 0));
            }

            $remainingToDeduct = $qty;

            $batches = $this->batches()
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;

                $deduct = min($batch->quantity, $remainingToDeduct);

                $batch->decrement('quantity', $deduct);
                $remainingToDeduct -= $deduct;

                StockTransaction::create([
                    'product_id' => $this->id,
                    'warehouse_id' => $warehouseId,
                    'product_batch_id' => $batch->id,
                    'ware_user_id' => $userId,
                    'type' => $type,
                    'quantity_change' => -$deduct,
                    'running_balance' => $stock->quantity - ($qty - $remainingToDeduct),
                    'remarks' => $remarks . " (Batch: {$batch->batch_number})"
                ]);
            }

            $stock->decrement('quantity', $qty);

            return true;
        });
    }

    /**
     * Get total warehouse quantity.
     */
    public function getWarehouseQuantity()
    {
        return ProductStock::where('product_id', $this->id)
            ->where('warehouse_id', 1)
            ->sum('quantity');
    }

    /**
     * Get total stores quantity.
     */
    public function getStoresQuantity()
    {
        return StoreStock::where('product_id', $this->id)->sum('quantity');
    }

    /**
     * Get total inventory (warehouse + stores).
     */
    public function getTotalQuantity()
    {
        return $this->getWarehouseQuantity() + $this->getStoresQuantity();
    }

    /**
     * Get warehouse stock value.
     */
    public function getWarehouseValue()
    {
        return $this->getWarehouseQuantity() * $this->cost_price;
    }

    /**
     * Get stores stock value.
     */
    public function getStoresValue()
    {
        return $this->getStoresQuantity() * $this->cost_price;
    }

    /**
     * Get total stock value.
     */
    public function getTotalValue()
    {
        return $this->getWarehouseValue() + $this->getStoresValue();
    }

    /**
     * Get expiring batches.
     */
    public function getExpiringBatches($days = 30)
    {
        return $this->batches()
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->get();
    }

    /**
     * Get expired batches.
     */
    public function getExpiredBatches()
    {
        return $this->batches()
            ->where('expiry_date', '<', now())
            ->get();
    }

    /**
     * Check if product has low stock.
     */
    public function isLowStock($threshold = 10)
    {
        return $this->getTotalQuantity() < $threshold;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
