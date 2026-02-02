<x-app-layout title="Start Stock Audit">
    <div class="container-fluid">
        
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Initiate Audit',
            'items' => [
                ['text' => 'Stock Audits', 'url' => route('warehouse.stock-control.audit.index')],
                ['text' => 'New Session']
            ]
        ])

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-clipboard-text-outline me-2"></i>Audit Setup</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('warehouse.stock-control.audit.store') }}" method="POST">
                            @csrf
                            
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert">
                                <i class="mdi mdi-information-outline fs-4 me-2"></i>
                                <div>
                                    <strong>Note:</strong> Starting a "Full" audit will snapshot current stock for ALL products in the warehouse. 
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Audit Type</label>
                                <select name="type" class="form-select bg-light" readonly>
                                    <option value="full" selected>Full Warehouse Inventory</option>
                                    {{-- Future: Add Category Wise --}}
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="E.g. Monthly Cycle Count for Jan 2026..."></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('warehouse.stock-control.audit.index') }}" class="btn btn-light border">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-play me-1"></i> Start Audit Session
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>