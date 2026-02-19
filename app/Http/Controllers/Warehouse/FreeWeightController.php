<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\FreeWeightPackage;
use App\Models\FreeWeightProduct;
use App\Models\PackagingEvent;
use App\Models\Product;
use App\Services\PackagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreeWeightController extends Controller
{
    protected PackagingService $packagingService;

    public function __construct(PackagingService $packagingService)
    {
        $this->packagingService = $packagingService;
    }

    /**
     * Dashboard: List all bulk products and their current weight.
     */
    public function index()
    {
        $bulkProducts = FreeWeightProduct::with(['product', 'packages.targetProduct'])
            ->where('is_active', true)
            ->get();

        $recentEvents = PackagingEvent::with(['freeWeightProduct.product', 'package', 'employee'])
            ->latest()
            ->take(10)
            ->get();

        return view('warehouse.free-weight.index', compact('bulkProducts', 'recentEvents'));
    }

    /**
     * Show form to register a new Bulk Product.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('product_name')->get();
        return view('warehouse.free-weight.create', compact('products'));
    }

    /**
     * Store a new Bulk Product registration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'bulk_weight'  => 'required|numeric|min:0.01',
            'unit'         => 'required|in:lbs,kg',
        ]);

        $warehouseId = Auth::user()->warehouse_id ?? 1;

        FreeWeightProduct::create([
            'product_id'   => $request->product_id,
            'warehouse_id' => $warehouseId,
            'bulk_weight'  => $request->bulk_weight,
            'unit'         => $request->unit,
            'is_active'    => true,
        ]);

        return redirect()->route('warehouse.free-weight.index')
            ->with('success', 'Bulk product registered successfully.');
    }

    /**
     * Show form to add a Package Definition to a Bulk Product.
     */
    public function createPackage(FreeWeightProduct $bulkProduct)
    {
        $bulkProduct->load('product');
        $products = Product::where('is_active', true)->orderBy('product_name')->get();
        return view('warehouse.free-weight.create-package', compact('bulkProduct', 'products'));
    }

    /**
     * Store a new Package Definition.
     */
    public function storePackage(Request $request, FreeWeightProduct $bulkProduct)
    {
        $request->validate([
            'package_name'      => 'required|string|max:100',
            'package_size'      => 'required|numeric|min:0.01',
            'unit'              => 'required|in:lbs,kg',
            'sku'               => 'required|string|unique:free_weight_packages,sku',
            'barcode'           => 'nullable|string',
            'target_product_id' => 'nullable|exists:products,id',
        ]);

        FreeWeightPackage::create([
            'free_weight_product_id' => $bulkProduct->id,
            'target_product_id'      => $request->target_product_id,
            'package_name'           => $request->package_name,
            'package_size'           => $request->package_size,
            'unit'                   => $request->unit,
            'sku'                    => $request->sku,
            'barcode'                => $request->barcode,
        ]);

        return redirect()->route('warehouse.free-weight.index')
            ->with('success', "Package definition '{$request->package_name}' added.");
    }

    /**
     * Show form to create a Packaging Event (Bulk -> Packs).
     */
    public function createEvent(FreeWeightProduct $bulkProduct)
    {
        $bulkProduct->load(['product', 'packages.targetProduct']);
        return view('warehouse.free-weight.create-event', compact('bulkProduct'));
    }

    /**
     * Process the Packaging Event.
     */
    public function storeEvent(Request $request, FreeWeightProduct $bulkProduct)
    {
        $request->validate([
            'package_id'        => 'required|exists:free_weight_packages,id',
            'packages_to_create' => 'required|integer|min:1',
            'notes'             => 'nullable|string|max:500',
        ]);

        try {
            $this->packagingService->executePackaging(
                $request->package_id,
                $request->packages_to_create,
                $request->notes
            );

            return redirect()->route('warehouse.free-weight.index')
                ->with('success', "Packaging event completed. {$request->packages_to_create} packages created.");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * History of all packaging events.
     */
    public function history()
    {
        $events = PackagingEvent::with(['freeWeightProduct.product', 'package', 'employee'])
            ->latest()
            ->paginate(20);

        return view('warehouse.free-weight.history', compact('events'));
    }
}
