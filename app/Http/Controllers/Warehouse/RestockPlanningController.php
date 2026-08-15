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
        $search = strtolower(trim($request->get('search', '')));

        $products = Product::whereNull('store_id')
            ->where('is_active', true)
            ->when($search !== '', function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where(DB::raw('LOWER(product_name)'), 'like', "%{$search}%")
                       ->orWhere(DB::raw('LOWER(upc)'), 'like', "%{$search}%");
                });
            })
            ->with(['stock' => function($q) {
                $q->where('warehouse_id', 1);
            }, 'category'])
            ->get();

        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $productIds = $products->pluck('id')->toArray();

        // Bulk load MinMax Levels
        $minMaxLevels = ProductMinMaxLevel::whereIn('product_id', $productIds)
            ->get()->keyBy('product_id');

        // Bulk load In Transit Data
        $inTransitData = PurchaseOrderItem::whereIn('product_id', $productIds)
            ->whereRaw('(requested_quantity - received_quantity) > 0')
            ->whereHas('purchaseOrder', function($q) {
                $q->whereNotIn('status', ['completed', 'cancelled']);
            })
            ->selectRaw('product_id, SUM(requested_quantity - received_quantity) as total_pending')
            ->groupBy('product_id')
            ->pluck('total_pending', 'product_id');

        // Bulk load Dispatch Volume
        $dispatchData = StorePurchaseOrderItem::whereIn('product_id', $productIds)
            ->where('dispatched_qty', '>', 0)
            ->whereHas('storePurchaseOrder', function($q) use ($thirtyDaysAgo) {
                $q->where('updated_at', '>=', $thirtyDaysAgo);
            })
            ->selectRaw('product_id, SUM(dispatched_qty) as total_dispatched')
            ->groupBy('product_id')
            ->pluck('total_dispatched', 'product_id');

        // Bulk load Latest Vendor Lead Time
        $latestPoItems = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'po.id', '=', 'poi.purchase_order_id')
            ->leftJoin('vendors as v', 'v.id', '=', 'po.vendor_id')
            ->whereIn('poi.product_id', $productIds)
            ->where('po.status', 'completed')
            ->select('poi.product_id', 'v.lead_time_days', 'po.created_at')
            ->orderBy('po.created_at', 'desc')
            ->get()
            ->groupBy('product_id')
            ->map(function($items) { return $items->first(); });

        // Calculate metrics for each product
        $planningData = $products->map(function ($product) use ($thirtyDaysAgo, $minMaxLevels, $inTransitData, $dispatchData, $latestPoItems) {
            $qtyInHand = $product->stock ? $product->stock->quantity : 0;
            
            // Expected Incoming from Vendor
            $inTransit = $inTransitData[$product->id] ?? 0;
                
            // Dispatched Volume (Last 30 days) to calculate burn rate
            $dispatchVolume = $dispatchData[$product->id] ?? 0;

            // Get last vendor and actual lead time if possible
            $vendorLeadTime = 7;
            if (isset($latestPoItems[$product->id]) && $latestPoItems[$product->id]->lead_time_days) {
                $vendorLeadTime = $latestPoItems[$product->id]->lead_time_days;
            }
                
            $dailyBurnRate = $dispatchVolume / 30;
            
            // Fast Moving Logic Algorithm:
            // 1. High dispatch volume (> 30 units a month) OR high daily burn
            $isFastMoving = false;
            if ($dispatchVolume > 50) {
                $isFastMoving = true;
            }

            $minMax = $minMaxLevels[$product->id] ?? null;
            $minLevel = $minMax ? $minMax->min_level : 0;
            $maxLevel = $minMax ? $minMax->max_level : 0;
            
            // Reorder Calculation
            $totalEffectiveStock = $qtyInHand + $inTransit;
            $recommendedOrder = 0;
            $suggestedDate = 'Adequate Inventory';
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
                'lead_time' => $vendorLeadTime,
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
        })->values();

        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $offset = ($page * $perPage) - $perPage;

        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $planningData->slice($offset, $perPage)->values(),
            $planningData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('warehouse.stock-control.restock_planning', ['planningData' => $paginatedData]);
    }
}
