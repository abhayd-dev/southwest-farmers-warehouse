<?php

namespace App\Http\Controllers\Warehouse\Helpdesk;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * Warehouse side of the Enquiry escalation flow: Store -> Warehouse ->
 * Main Super Admin. Only shows enquiries a store has already escalated —
 * the store keeps handling brand-new ones itself.
 */
class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Enquiry::whereIn('status', [
            Enquiry::STATUS_ESCALATED_WAREHOUSE,
            Enquiry::STATUS_ESCALATED_ADMIN,
        ])->latest('escalated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enquiries = $query->paginate(20)->withQueryString();

        return view('warehouse.enquiries.index', compact('enquiries'));
    }

    public function show($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        return view('warehouse.enquiries.show', compact('enquiry'));
    }

    /**
     * Escalation tier 2: Warehouse -> Main Super Admin specifically.
     */
    public function escalateToAdmin($id)
    {
        $enquiry = Enquiry::findOrFail($id);

        if (!$enquiry->isEscalatedToWarehouse()) {
            return back()->with('error', 'This enquiry is not at the Warehouse stage.');
        }

        $enquiry->update([
            'status' => Enquiry::STATUS_ESCALATED_ADMIN,
            'escalated_to_admin_at' => now(),
        ]);

        NotificationService::sendToSuperAdmins(
            'Enquiry Escalated to You',
            "Enquiry from {$enquiry->name} (\"{$enquiry->subject}\") has been escalated to Super Admin by " . (auth()->user()->name ?? 'a warehouse staff member') . '.',
            'danger',
            route('warehouse.enquiries.show', $enquiry->id)
        );

        return back()->with('success', 'Enquiry escalated to the Super Admin.');
    }

    public function resolve(Request $request, $id)
    {
        $request->validate(['resolution_notes' => 'nullable|string']);

        $enquiry = Enquiry::findOrFail($id);
        $enquiry->update([
            'status' => Enquiry::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution_notes' => $request->resolution_notes,
        ]);

        return redirect()->route('warehouse.enquiries.index')->with('success', 'Enquiry marked as resolved.');
    }
}
