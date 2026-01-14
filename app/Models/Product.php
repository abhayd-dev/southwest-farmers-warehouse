<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'ware_user_id',
        'product_option_id',
        'category_id',
        'subcategory_id',
        'product_name',
        'sku',
        'barcode',
        'unit',
        'purchase_unit',
        'conversion_factor',
        'is_batch_active',
        'tax_percent',
        'price',
        'cost_price',
        'icon',
        'is_active'
    ];

    // ... (Existing relationships same rahenge) ...

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

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class)->latest();
    }
    
    public function user()
    {
        return $this->belongsTo(WareUser::class, 'ware_user_id');
    }

    // --- UPDATED STOCK LOGIC ---

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
                'cost_price' => $batchData['cost_price'] ?? $this->cost_price,
                'quantity' => $qty,
            ]);

            // 2. Update Total Snapshot
            $stock = ProductStock::firstOrCreate(
                ['product_id' => $this->id, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );
            $stock->increment('quantity', $qty);

            // 3. Log Transaction (UPDATED: user_id -> ware_user_id)
            StockTransaction::create([
                'product_id' => $this->id,
                'warehouse_id' => $warehouseId,
                'product_batch_id' => $batch->id,
                'ware_user_id' => $userId, // Change here
                'type' => $type,
                'quantity_change' => $qty,
                'running_balance' => $stock->quantity,
                'remarks' => $remarks
            ]);

            return $batch;
        });
    }

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

                // Log Transaction (UPDATED: user_id -> ware_user_id)
                StockTransaction::create([
                    'product_id' => $this->id,
                    'warehouse_id' => $warehouseId,
                    'product_batch_id' => $batch->id,
                    'ware_user_id' => $userId, // Change here
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
}