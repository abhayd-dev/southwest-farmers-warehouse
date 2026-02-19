<x-app-layout title="Create New Pallet">
    <div class="container-fluid p-3 p-md-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="{{ route('warehouse.pallets.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="fw-bold mb-0">Create New Pallet</h4>
                        <small class="text-muted">Maximum weight: 2,200 lbs per pallet</small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('warehouse.pallets.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Link to Store PO (Optional)</label>
                                <select name="store_po_id" class="form-select">
                                    <option value="">-- Standalone Pallet --</option>
                                    @foreach($pendingPOs as $po)
                                        <option value="{{ $po->id }}" {{ $selectedPoId == $po->id ? 'selected' : '' }}>
                                            {{ $po->po_number }} — {{ $po->store->store_name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Link this pallet to an approved Store PO for dispatch tracking.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Department (Optional)</label>
                                <select name="department_id" class="form-select">
                                    <option value="">-- No Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Max Weight (lbs)</label>
                                <input type="number" name="max_weight" class="form-control"
                                       value="2200" min="1" max="2200" step="1">
                                <div class="form-text">Default is 2,200 lbs. Do not exceed this limit.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('warehouse.pallets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-check me-1"></i> Create Pallet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
