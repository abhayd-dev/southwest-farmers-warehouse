<x-app-layout title="Add Package Size">
    <div class="container-fluid p-3 p-md-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="{{ route('warehouse.free-weight.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="fw-bold mb-0">Add Package Size</h4>
                        <small class="text-muted">
                            Define a package size for: <strong>{{ $bulkProduct->product->product_name ?? 'N/A' }}</strong>
                        </small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('warehouse.free-weight.package.store', $bulkProduct->id) }}" method="POST" class="needs-validation" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Package Name <span class="text-danger">*</span></label>
                                <input type="text" name="package_name" class="form-control"
                                       value="{{ old('package_name') }}" placeholder="e.g. 10 lb Bag" required>
                                <div class="invalid-feedback">Please enter a package name.</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-8">
                                    <label class="form-label fw-semibold">Package Size <span class="text-danger">*</span></label>
                                    <input type="number" name="package_size" step="0.01" min="0.01"
                                           class="form-control" value="{{ old('package_size') }}"
                                           placeholder="e.g. 10" required>
                                    <div class="form-text">How much bulk weight goes into one package.</div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                    <select name="unit" class="form-select" required>
                                        <option value="lbs" {{ old('unit') == 'lbs' ? 'selected' : '' }}>lbs</option>
                                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">SKU <span class="text-danger">*</span></label>
                                    <input type="text" name="sku" class="form-control"
                                           value="{{ old('sku') }}" placeholder="e.g. LENTIL-10LB" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Barcode</label>
                                    <input type="text" name="barcode" class="form-control"
                                           value="{{ old('barcode') }}" placeholder="Optional">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Link to Sellable Product</label>
                                <select name="target_product_id" class="form-select">
                                    <option value="">-- None (standalone package) --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('target_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->product_name }} ({{ $product->sku ?? 'No SKU' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">When packaging happens, stock for this product will be increased automatically.</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('warehouse.free-weight.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-check me-1"></i> Save Package Size
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
