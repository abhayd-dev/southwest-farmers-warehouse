<x-app-layout title="Register Bulk Product">
    <div class="container-fluid p-3 p-md-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="{{ route('warehouse.free-weight.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="fw-bold mb-0">Register Bulk Product</h4>
                        <small class="text-muted">Add a new bulk weight item (e.g., 5000 lbs of Lentils)</small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('warehouse.free-weight.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->product_name }} ({{ $product->sku ?? 'No SKU' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a product.</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-8">
                                    <label class="form-label fw-semibold">Initial Bulk Weight <span class="text-danger">*</span></label>
                                    <input type="number" name="bulk_weight" step="0.01" min="0.01"
                                           class="form-control" value="{{ old('bulk_weight') }}"
                                           placeholder="e.g. 5000" required>
                                    <div class="invalid-feedback">Please enter the bulk weight.</div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                    <select name="unit" class="form-select" required>
                                        <option value="lbs" {{ old('unit') == 'lbs' ? 'selected' : '' }}>lbs</option>
                                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('warehouse.free-weight.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-check me-1"></i> Register Bulk Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
