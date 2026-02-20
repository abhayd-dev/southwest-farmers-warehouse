<?php


namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\StorePurchaseOrderItem;
use App\Models\ProductMinMaxLevel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RestockPlanningController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::whereNull('store_id')
            ->where('is_active', true)
            ->with(['stock' => function($q) {
                $q->where('warehouse_id', 1);
            }, 'category'])
            ->get();

        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Calculate metrics for each product
        $planningData = $products->map(function ($product) use ($thirtyDaysAgo) {
            $qtyInHand = $product->stock ? $product->stock->quantity : 0;
            
            // Expected Incoming from Vendor
            $inTransit = PurchaseOrderItem::where('product_id', $product->id)
                ->whereRaw('(requested_quantity - received_quantity) > 0')
                ->whereHas('purchaseOrder', function($q) {
                    $q->whereNotIn('status', ['completed', 'cancelled']);
                })
                ->selectRaw('SUM(requested_quantity - received_quantity) as total_pending')
                ->value('total_pending') ?? 0;
                
            // Dispatched Volume (Last 30 days) to calculate burn rate
            $dispatchVolume = StorePurchaseOrderItem::where('product_id', $product->id)
                ->where('dispatched_qty', '>', 0)
                ->whereHas('storePurchaseOrder', function($q) use ($thirtyDaysAgo) {
                    $q->where('updated_at', '>=', $thirtyDaysAgo);
                })->sum('dispatched_qty');
                
            $dailyBurnRate = $dispatchVolume / 30;
            
            // Fast Moving Logic Algorithm:
            // 1. High dispatch volume (> 30 units a month) OR high daily burn
            $isFastMoving = false;
            if ($dispatchVolume > 50) {
                $isFastMoving = true;
            }

            $minMax = ProductMinMaxLevel::where('product_id', $product->id)->first();
            $minLevel = $minMax ? $minMax->min_level : 0;
            $maxLevel = $minMax ? $minMax->max_level : 0;
            
            // Reorder Calculation
            $totalEffectiveStock = $qtyInHand + $inTransit;
            $recommendedOrder = 0;
            $suggestedDate = 'Adequate Stock';
            $actionRequired = false;

            if ($totalEffectiveStock <= $minLevel && $minLevel > 0) {
                $recommendedOrder = max(0, $maxLevel - $totalEffectiveStock);
                if ($recommendedOrder > 0) {
                    $actionRequired = true;
                    $suggestedDate = 'Order ASAP';
                }
            } elseif ($dailyBurnRate > 0 && $minLevel > 0) {
                // How many days until we hit minimum?
                $daysUntilMin = ($totalEffectiveStock - $minLevel) / $dailyBurnRate;
                if ($daysUntilMin < 14) {
                    $suggestedDate = Carbon::now()->addDays(floor($daysUntilMin))->format('Y-m-d');
                    $recommendedOrder = max(0, $maxLevel - min($totalEffectiveStock, $minLevel));
                }
            }

            return (object) [
                'product' => $product,
                'qty_in_hand' => $qtyInHand,
                'in_transit' => $inTransit,
                'cost' => $product->cost_price,
                'lead_time' => 7, // Default 7 days lead time assumed for suppliers
                'recommended_order' => $recommendedOrder,
                'suggested_date' => $suggestedDate,
                'is_fast_moving' => $isFastMoving,
                'action_required' => $actionRequired,
                'daily_burn' => $dailyBurnRate
            ];
        });

        // Sort by those needing action first, then fast moving
        $planningData = $planningData->sortByDesc(function ($item) {
            return ($item->action_required ? 100 : 0) + ($item->is_fast_moving ? 10 : 0);
        });

        return view('warehouse.stock-control.restock_planning', compact('planningData'));
    }
}
