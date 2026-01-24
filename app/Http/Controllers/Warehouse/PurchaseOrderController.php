<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Product;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class PurchaseOrderController extends Controller
{
    protected $poService;

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PurchaseOrder::with('vendor', 'creator')->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('vendor_name', fn($row) => $row->vendor->name)
                ->addColumn('total_amount', fn($row) => '₹ ' . number_format($row->total_amount, 2))
                ->addColumn('progress', function ($row) {
                    $color = $row->progress == 100 ? 'success' : 'primary';
                    return '<div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-'.$color.'" role="progressbar" style="width: '.$row->progress.'%"></div>
                            </div>
                            <small class="text-muted">'.$row->progress.'% Received</small>';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'draft' => 'secondary', 'ordered' => 'info',
                        'partial' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'
                    ];
                    return '<span class="badge bg-' . ($badges[$row->status] ?? 'secondary') . '">' . strtoupper($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = route('warehouse.purchase-orders.show', $row->id);
                    return '<a href="' . $viewUrl . '" class="btn btn-sm btn-outline-primary">View / Receive</a>';
                })
                ->rawColumns(['progress', 'status_badge', 'action'])
                ->make(true);
        }

        return view('warehouse.purchase-orders.index');
    }

    public function create()
    {
        $vendors = Vendor::active()->get();
        // Only get Warehouse Products
        $products = Product::warehouse()->active()->select('id', 'product_name', 'sku', 'cost_price')->get();
        
        return view('warehouse.purchase-orders.create', compact('vendors', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        try {
            $po = $this->poService->createPO($request->all());
            return redirect()->route('warehouse.purchase-orders.show', $po->id)
                ->with('success', 'Purchase Order created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating PO: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['vendor', 'items.product', 'creator']);
        return view('warehouse.purchase-orders.show', compact('purchaseOrder'));
    }

    public function markOrdered(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') abort(403);
        
        $purchaseOrder->update(['status' => 'ordered']);
        return back()->with('success', 'PO marked as Ordered. Sent to Vendor.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        set_time_limit(300);

        $request->validate([
            'invoice_number' => 'required|string',
            'items' => 'required|array',
        ]);

        try {
            $this->poService->receiveItems($purchaseOrder->id, $request->items, $request->invoice_number);
            return back()->with('success', 'Stock received successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Receive failed: ' . $e->getMessage());
        }
    }

    public function printLabels(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items.product', 'vendor']);
        
        $items = $purchaseOrder->items->filter(function($item) {
            return $item->received_quantity > 0;
        });

        if ($items->isEmpty()) {
            return back()->with('error', 'No received items found to print labels for.');
        }

        // Barcode Generator Instance
        $generator = new BarcodeGeneratorPNG();

        $pdf = Pdf::loadView('warehouse.purchase-orders.labels', compact('purchaseOrder', 'items', 'generator'));
        
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('PO-'.$purchaseOrder->po_number.'-Labels.pdf');
    }
}