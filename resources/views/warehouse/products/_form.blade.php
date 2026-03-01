<div class="card border-0 shadow-sm mb-4" id="productDetailsCard" style="display: block;"> {{-- Ensure visible --}}
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="mdi mdi-information text-primary"></i>
            Product Details
        </h5>
    </div>
    <div class="card-body p-4">
        {{-- Icon Upload Section --}}
        <div class="mb-4 text-center">
            <label for="iconInput" class="position-relative d-inline-block cursor-pointer">
                <img id="iconPreview"
                    src="{{ isset($product) && $product->icon ? Storage::url($product->icon) : asset('assets/images/placeholder.svg') }}"
                    class="rounded border shadow-sm object-fit-cover" width="120" height="120">
                <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm border">
                    <i class="mdi mdi-camera text-primary"></i>
                </div>
                <input type="file" name="icon" id="iconInput" class="d-none" accept="image/*"
                    onchange="previewImage(event)">
            </label>
        </div>

        <div class="row g-4">

            {{-- DEPARTMENT DROPDOWN --}}
            <div class="col-md-12">
                <label class="form-label fw-semibold">
                    Department <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-domain text-muted"></i>
                    </span>
                    <select name="department_id" class="form-select border-start-0" required>
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ isset($product) && $product->department_id == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="invalid-feedback">Please select a department.</div>
            </div>

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
                <label class="form-label fw-semibold">UPC</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-barcode text-muted"></i>
                    </span>
                    <input type="text" name="upc" id="upcInput" class="form-control border-start-0"
                        value="{{ old('upc', $product->upc ?? '') }}" placeholder="Scan or generate UPC">
                    <button class="btn btn-outline-secondary" type="button" id="generateUpcBtn" title="Generate UPC">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Barcode <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-barcode-scan text-muted"></i>
                    </span>
                    <input type="text" name="barcode" id="barcodeInput" class="form-control border-start-0 bg-light"
                        value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="Auto-generated from UPC"
                        readonly required>
                </div>
                <div class="mt-2" style="height: 50px;">
                    <svg id="barcodeDisplay"></svg>
                </div>
                <div class="invalid-feedback">Please provide a barcode.</div>
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

            <div class="col-12 mt-2">
                <hr>
                <h6 class="fw-semibold text-primary mb-3"><i class="mdi mdi-currency-usd"></i>
                    Pricing Details</h6>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Tax Percentage (%)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">%</span>
                    <input type="number" step="0.01" name="tax_percent" class="form-control border-start-0"
                        value="{{ old('tax_percent', $product->tax_percent ?? '0.00') }}" placeholder="0.00">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Warehouse Cost (WHSE Cost)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="cost_price" class="form-control border-start-0"
                        value="{{ old('cost_price', $product->cost_price ?? '') }}" placeholder="0.00">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Warehouse Price (WHSE Price) <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="price" class="form-control border-start-0"
                        value="{{ old('price', $product->price ?? '') }}" placeholder="0.00" required>
                </div>
                <div class="invalid-feedback">Please enter a price.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Retail Price</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="retail_price" class="form-control border-start-0"
                        value="{{ old('retail_price', $product->retail_price ?? '') }}" placeholder="0.00">
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 align-items-start pt-2">
                        <i class="mdi mdi-text text-muted"></i>
                    </span>
                    <textarea name="description" class="form-control border-start-0" rows="3"
                        placeholder="Enter product description (optional)">{{ old('description', $product->description ?? '') }}</textarea>

                    <div class="col-12 mt-4">
                        <hr>
                        <h6 class="fw-semibold text-primary mb-3"><i class="mdi mdi-package-variant-closed"></i>
                            Pallet Arrangement & Carton Details</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Carton Length (in)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="mdi mdi-arrow-left-right text-muted"></i></span>
                            <input type="number" step="0.01" name="carton_length"
                                class="form-control border-start-0"
                                value="{{ old('carton_length', $product->carton_length ?? '') }}" placeholder="0.00">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Carton Width (in)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="mdi mdi-arrow-expand-horizontal text-muted"></i></span>
                            <input type="number" step="0.01" name="carton_width"
                                class="form-control border-start-0"
                                value="{{ old('carton_width', $product->carton_width ?? '') }}" placeholder="0.00">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Carton Height (in)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="mdi mdi-arrow-up-down text-muted"></i></span>
                            <input type="number" step="0.01" name="carton_height"
                                class="form-control border-start-0"
                                value="{{ old('carton_height', $product->carton_height ?? '') }}" placeholder="0.00">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Units Per Carton <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="mdi mdi-apps text-muted"></i></span>
                            <input type="number" min="1" step="1" name="units_per_carton"
                                class="form-control border-start-0"
                                value="{{ old('units_per_carton', $product->units_per_carton ?? '1') }}" required>
                        </div>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch fs-5 mb-1">
                            <input class="form-check-input" type="checkbox" name="is_stackable" value="1"
                                id="isStackableSwitch"
                                {{ old('is_stackable', $product->is_stackable ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fs-6 fw-semibold" for="isStackableSwitch">Is
                                Stackable?</label>
                        </div>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch fs-5 mb-1">
                            <input class="form-check-input" type="checkbox" name="is_fragile" value="1"
                                id="isFragileSwitch"
                                {{ old('is_fragile', $product->is_fragile ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label fs-6 fw-semibold text-danger" for="isFragileSwitch">Is
                                Fragile?</label>
                        </div>
                    </div>

                </div>
            </div>
        </div>
