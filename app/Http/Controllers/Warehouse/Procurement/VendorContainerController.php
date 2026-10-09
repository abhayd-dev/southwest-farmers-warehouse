<?php

namespace App\Http\Controllers\Warehouse\Procurement;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorContainerBalance;
use App\Services\VendorContainerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Client ticket 23, requirement 3: reconcile pallet/divider inventory per
 * vendor. Receiving/returning them happens on the Receive Order screen;
 * this page is the running balance across every vendor, with a manual
 * correction when a physical count disagrees with it.
 */
class VendorContainerController extends Controller
{
    protected $containerService;

    public function __construct(VendorContainerService $containerService)
    {
        $this->containerService = $containerService;
    }

    public function index()
    {
        $vendors = Vendor::active()->orderBy('name')->get();
        $balances = VendorContainerBalance::whereIn('vendor_id', $vendors->pluck('id'))
            ->get()->keyBy('vendor_id');

        return view('warehouse.vendors.containers', compact('vendors', 'balances'));
    }

    public function reconcile(Request $request, Vendor $vendor)
    {
        abort_unless(auth()->user()->can('manage_vendors'), 403);

        $request->validate([
            'pallet_count' => 'required|numeric|min:0',
            'divider_count' => 'required|numeric|min:0',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->containerService->reconcile(
                $vendor,
                (float) $request->pallet_count,
                (float) $request->divider_count,
                auth()->user()->name,
                $request->reason
            );

            return back()->with('success', "Container balance reconciled for {$vendor->name}.");
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Vendor container reconciliation failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with(\App\Support\ErrorMessage::flash($e, 'Something went wrong. Please try again later.'));
        }
    }
}
