<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\ImportTask;
use App\Imports\VendorImport;
use App\Exports\Samples\VendorSampleExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
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
                    
                    return '<div class="action-btns">
                                <a href="'.$editUrl.'" class="btn btn-sm btn-outline-primary btn-edit" title="Edit">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                <form action="'.$deleteUrl.'" method="POST" class="d-inline delete-form">
                                    '.csrf_field().'
                                    '.method_field('DELETE').'
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete" title="Delete">
                                        <i class="mdi mdi-trash-can"></i>
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
            \Illuminate\Support\Facades\Log::error('Vendor status change failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Something went wrong. Please try again later.'], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,csv',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors'  => $validator->errors(),
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            // Create Import Task
            $task = ImportTask::create([
                'user_id'   => Auth::id(),
                'type'      => 'Vendor',
                'status'    => ImportTask::STATUS_PENDING,
                'file_name' => $request->file('file')->getClientOriginalName(),
            ]);

            Excel::import(
                new VendorImport(Auth::id(), $task->id),
                $request->file
            );

            $task->refresh();

            $skippedErrors = [];
            if ($task->error_message) {
                $decoded = json_decode($task->error_message, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $skippedErrors = $decoded;
                } else {
                    $skippedErrors = [$task->error_message];
                }
            }

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Import completed!',
                    'task_id' => $task->id,
                    'skipped' => $skippedErrors
                ]);
            }

            if (!empty($skippedErrors)) {
                return back()->with('success', 'Import completed with warnings!')
                             ->with('import_skipped_errors', $skippedErrors);
            }

            return back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vendor import failed: ' . $e->getMessage(), ['exception' => $e]);
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.',
                ], 500);
            }
            return back()->with('error', 'Something went wrong. Please try again later.');
        }
    }

    public function sample()
    {
        return Excel::download(new VendorSampleExport, 'vendors-sample.xlsx');
    }
}