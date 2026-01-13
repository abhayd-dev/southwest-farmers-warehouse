<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="mdi mdi-layers text-primary"></i>
            Product Option (Optional)
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-info d-flex align-items-start mb-3 border-0">
            <i class="mdi mdi-information-outline fs-5 me-2"></i>
            <div class="small">
                <strong>Tip:</strong> Select a product option to auto-fill the fields below, or click "Add New" for
                manual entry.
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Select Product Option</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-package-variant text-muted"></i>
                    </span>
                    <select name="product_option_id" id="productOptionSelect" class="form-select border-start-0">
                        <option value="">-- Select Option --</option>
                        @foreach ($options as $o)
                            <option value="{{ $o->id }}"
                                {{ isset($product) && $product->product_option_id == $o->id ? 'selected' : '' }}>
                                {{ $o->option_name }} ({{ $o->sku }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-primary" id="toggleManualEntry">
                        <i class="mdi mdi-plus-circle-outline"></i> Add New
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" id="productDetailsCard" style="display: none;">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="mdi mdi-information text-primary"></i>
            Product Details
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Category <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-shape text-muted"></i>
                    </span>
                    <select name="category_id" id="categorySelect" class="form-select border-start-0" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ isset($product) && $product->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="invalid-feedback">Please select a category.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Subcategory <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-tag text-muted"></i>
                    </span>
                    <select name="subcategory_id" id="subcategorySelect" class="form-select border-start-0" required>
                        <option value="">Select Subcategory</option>
                        @if (isset($product) && isset($subcategories))
                            @foreach ($subcategories as $sub)
                                <option value="{{ $sub->id }}"
                                    {{ $product->subcategory_id == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="invalid-feedback">Please select a subcategory.</div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">
                    Product Name <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-cube text-muted"></i>
                    </span>
                    <input type="text" name="product_name" class="form-control border-start-0"
                        value="{{ old('product_name', $product->product_name ?? '') }}"
                        placeholder="Enter product name" required>
                </div>
                <div class="invalid-feedback">Please enter a product name.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">SKU</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-barcode text-muted"></i>
                    </span>
                    <input type="text" name="sku" class="form-control border-start-0"
                        value="{{ old('sku', $product->sku ?? '') }}" placeholder="Stock Keeping Unit">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Barcode</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-barcode-scan text-muted"></i>
                    </span>
                    <input type="text" name="barcode" class="form-control border-start-0"
                        value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="Scan or enter barcode">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Unit <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-cube-outline text-muted"></i>
                    </span>
                    <select name="unit" class="form-select border-start-0" required>
                        <option value="">Select Unit</option>
                        <option value="kg" {{ isset($product) && $product->unit == 'kg' ? 'selected' : '' }}>
                            Kilogram (KG)
                        </option>
                        <option value="pcs" {{ isset($product) && $product->unit == 'pcs' ? 'selected' : '' }}>
                            Pieces (PCS)
                        </option>
                        <option value="ltr" {{ isset($product) && $product->unit == 'ltr' ? 'selected' : '' }}>
                            Liter (LTR)
                        </option>
                        <option value="box" {{ isset($product) && $product->unit == 'box' ? 'selected' : '' }}>
                            Box
                        </option>
                    </select>
                </div>
                <div class="invalid-feedback">Please select a unit.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Price <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="price" class="form-control border-start-0"
                        value="{{ old('price', $product->price ?? '') }}" placeholder="0.00" required>
                </div>
                <div class="invalid-feedback">Please enter a price.</div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 align-items-start pt-2">
                        <i class="mdi mdi-text text-muted"></i>
                    </span>
                    <textarea name="description" class="form-control border-start-0" rows="3"
                        placeholder="Enter product description (optional)">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
            </div>

        </div>
    </div>
</div>
