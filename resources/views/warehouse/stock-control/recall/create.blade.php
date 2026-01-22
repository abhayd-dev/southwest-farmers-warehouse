<x-app-layout title="Initiate Recall Request">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Initiate Recall Request'])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="mdi mdi-undo-variant text-warning me-2"></i> Initiate Recall Request
        </h4>
        <a href="{{ route('warehouse.stock-control.recall.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">New Recall Request Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('warehouse.stock-control.recall.store') }}" class="needs-validation" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Store <span class="text-danger">*</span></label>
                        <select name="store_id" class="form-select" required>
                            <option value="">Select Store</option>
                            @foreach(\App\Models\StoreDetail::where('is_active', true)->get() as $store)
                                <option value="{{ $store->id }}">{{ $store->store_name }} ({{ $store->city ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a store.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach(\App\Models\Product::where('is_active', true)->get() as $product)
                                <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->sku ?? 'No SKU' }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a product.</div>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label">Requested Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="requested_quantity" class="form-control" min="1" required>
                    <div class="invalid-feedback">Quantity must be at least 1.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <select name="reason" class="form-select" required>
                        <option value="">Select Reason</option>
                        <option value="near_expiry">Near Expiry</option>
                        <option value="quality_issue">Quality Issue</option>
                        <option value="overstock">Overstock</option>
                        <option value="damage">Damage / Defective</option>
                        <option value="other">Other</option>
                    </select>
                    <div class="invalid-feedback">Please select a reason.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea name="reason_remarks" class="form-control" rows="3" placeholder="Additional details or notes..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('warehouse.stock-control.recall.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-send me-1"></i> Create Recall Request
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Bootstrap form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})();
</script>
@endpush

</x-app-layout>