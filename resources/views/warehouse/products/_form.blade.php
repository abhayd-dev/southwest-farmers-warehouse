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

            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    SKU (Optional)
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-identifier text-muted"></i>
                    </span>
                    <input type="text" name="sku" class="form-control border-start-0"
                        value="{{ old('sku', $product->sku ?? '') }}" placeholder="Enter SKU">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">PLU Code (Optional)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-numeric text-muted"></i>
                    </span>
                    <input type="text" name="plu_code" class="form-control border-start-0"
                        value="{{ old('plu_code', $product->plu_code ?? '') }}" placeholder="Enter PLU code">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">UPC <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="mdi mdi-barcode text-muted"></i>
                    </span>
                    <input type="text" name="upc" id="upcInput" class="form-control border-start-0"
                        value="{{ old('upc', $product->upc ?? '') }}" placeholder="Scan or generate UPC" required>
                    <button class="btn btn-outline-secondary" type="button" id="generateUpcBtn"
                        title="Generate UPC">
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
                    <input type="text" name="barcode" id="barcodeInput"
                        class="form-control border-start-0 bg-light"
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
                        <option value="doz" {{ isset($product) && $product->unit == 'doz' ? 'selected' : '' }}>
                            Dozen (doz)</option>
                        <option value="ea" {{ isset($product) && $product->unit == 'ea' ? 'selected' : '' }}>Each
                            (ea)</option>
                        <option value="gal" {{ isset($product) && $product->unit == 'gal' ? 'selected' : '' }}>
                            Gallon (gal)</option>
                        <option value="g" {{ isset($product) && $product->unit == 'g' ? 'selected' : '' }}>Grams
                            (g)</option>
                        <option value="in" {{ isset($product) && $product->unit == 'in' ? 'selected' : '' }}>Inch
                            (in)</option>
                        <option value="kg" {{ isset($product) && $product->unit == 'kg' ? 'selected' : '' }}>
                            Kilograms (kg)</option>
                        <option value="lt" {{ isset($product) && $product->unit == 'lt' ? 'selected' : '' }}>
                            Liters (lt)</option>
                        <option value="oz" {{ isset($product) && $product->unit == 'oz' ? 'selected' : '' }}>Ounce
                            (oz)</option>
                        <option value="pk" {{ isset($product) && $product->unit == 'pk' ? 'selected' : '' }}>Pack
                            (pk)</option>
                        <option value="pc" {{ isset($product) && $product->unit == 'pc' ? 'selected' : '' }}>Piece
                            (pc)</option>
                        <option value="pt" {{ isset($product) && $product->unit == 'pt' ? 'selected' : '' }}>Pint
                            (pt)</option>
                        <option value="lb" {{ isset($product) && $product->unit == 'lb' ? 'selected' : '' }}>Pound
                            (lb)</option>
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
                <label class="form-label fw-semibold">Warehouse Markup Percentage (%) <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">%</span>
                    <input type="number" step="0.01" name="warehouse_markup_percentage"
                        id="warehouseMarkupInput" class="form-control border-start-0"
                        value="{{ old('warehouse_markup_percentage', $product->warehouse_markup_percentage ?? '0.00') }}"
                        placeholder="0.00" required>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Warehouse Cost (WHSE Cost) <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="cost_price" id="costPriceInput"
                        class="form-control border-start-0"
                        value="{{ old('cost_price', $product->cost_price ?? '') }}" placeholder="0.00" required>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Warehouse Price (WHSE Price) <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="price" id="warehousePriceInput"
                        class="form-control border-start-0" value="{{ old('price', $product->price ?? '') }}"
                        placeholder="0.00" required>
                </div>
                <div class="invalid-feedback">Please enter a price.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Store Markup Percentage (%) <span
                        class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">%</span>
                    <input type="number" step="0.01" name="store_markup_percentage" id="storeMarkupInput"
                        class="form-control border-start-0"
                        value="{{ old('store_markup_percentage', $product->store_markup_percentage ?? '0.00') }}"
                        placeholder="0.00" required>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Store Retail Price <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="store_retail_price" id="storeRetailPriceInput"
                        class="form-control border-start-0"
                        value="{{ old('store_retail_price', $product->store_retail_price ?? '') }}"
                        placeholder="0.00" required>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Manual Override Price</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">$</span>
                    <input type="number" step="0.01" name="manual_override_price"
                        class="form-control border-start-0"
                        value="{{ old('manual_override_price', $product->manual_override_price ?? '') }}"
                        placeholder="0.00">
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
                </div>
            </div>

            <div class="col-12 mt-2">
                <hr>
                <h6 class="fw-semibold text-primary mb-3"><i class="mdi mdi-package-variant-closed"></i>
                    Pallet Arrangement & Case Details</h6>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Case Length (in)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i
                            class="mdi mdi-arrow-left-right text-muted"></i></span>
                    <input type="number" step="0.01" name="carton_length" class="form-control border-start-0"
                        value="{{ old('carton_length', $product->carton_length ?? '') }}" placeholder="0.00">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Case Width (in)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i
                            class="mdi mdi-arrow-expand-horizontal text-muted"></i></span>
                    <input type="number" step="0.01" name="carton_width" class="form-control border-start-0"
                        value="{{ old('carton_width', $product->carton_width ?? '') }}" placeholder="0.00">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Case Height (in)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i
                            class="mdi mdi-arrow-up-down text-muted"></i></span>
                    <input type="number" step="0.01" name="carton_height" class="form-control border-start-0"
                        value="{{ old('carton_height', $product->carton_height ?? '') }}" placeholder="0.00">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Units Per Case <span class="text-danger">*</span></label>
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
                    <label class="form-check-label fs-6 fw-semibold" for="isStackableSwitch">Is Stackable?</label>
                </div>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch fs-5 mb-1">
                    <input class="form-check-input" type="checkbox" name="is_fragile" value="1"
                        id="isFragileSwitch" {{ old('is_fragile', $product->is_fragile ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fs-6 fw-semibold text-danger" for="isFragileSwitch">Is
                        Fragile?</label>
                </div>
            </div>

        </div> {{-- End Row --}}
    </div> {{-- End Card Body --}}
</div> {{-- End Card --}}
