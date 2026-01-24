<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        // 1. If Ajax Request -> Return DataTable
        if ($request->ajax()) {
            $query = Vendor::query()->latest();

            // Search Filter (Postgres ilike)
            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('contact_person', 'ilike', "%{$search}%")
                      ->orWhere('email', 'ilike', "%{$search}%")
                      ->orWhere('phone', 'ilike', "%{$search}%");
                });
            }

            // Status Filter
            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('contact_info', function ($row) {
                    $html = '<div class="d-flex flex-column">';
                    if($row->contact_person) $html .= '<span class="fw-semibold text-dark">'.$row->contact_person.'</span>';
                    if($row->email) $html .= '<small class="text-muted"><i class="mdi mdi-email-outline me-1"></i>'.$row->email.'</small>';
                    if($row->phone) $html .= '<small class="text-muted"><i class="mdi mdi-phone-outline me-1"></i>'.$row->phone.'</small>';
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->is_active ? 'checked' : '';
                    return '<div class="form-check form-switch d-inline-block">
                                <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                    data-id="'.$row->id.'" '.$checked.'>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('warehouse.vendors.edit', $row->id);
                    $deleteUrl = route('warehouse.vendors.destroy', $row->id);
                    
                    return '<div class="text-end">
                                <a href="'.$editUrl.'" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                <form action="'.$deleteUrl.'" method="POST" class="d-inline delete-form">
                                    '.csrf_field().'
                                    '.method_field('DELETE').'
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </form>
                            </div>';
                })
                ->rawColumns(['contact_info', 'status', 'action'])
                ->make(true);
        }

        // 2. Return View
        return view('warehouse.vendors.index');
    }

    public function create()
    {
        return view('warehouse.vendors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'lead_time_days' => 'nullable|integer|min:0',
        ]);

        Vendor::create($request->all());

        return redirect()->route('warehouse.vendors.index')
            ->with('success', 'Vendor created successfully');
    }

    public function edit(Vendor $vendor)
    {
        return view('warehouse.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'lead_time_days' => 'nullable|integer|min:0',
        ]);

        $vendor->update($request->all());

        return redirect()->route('warehouse.vendors.index')
            ->with('success', 'Vendor updated successfully');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return back()->with('success', 'Vendor deleted successfully');
    }

    public function changeStatus(Request $request)
    {
        try {
            $vendor = Vendor::findOrFail($request->id);
            $vendor->update(['is_active' => $request->status]);
            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating status'], 500);
        }
    }
}