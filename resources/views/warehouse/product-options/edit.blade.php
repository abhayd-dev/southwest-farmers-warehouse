<x-app-layout title="Edit Product Option">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.partials.breadcrumb', ['title' => 'Edit Product Option'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-pencil-circle text-warning"></i>
                        Edit Product Option
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.product-options.update', $productOption) }}" enctype="multipart/form-data"
            class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="row">
                {{-- MAIN FORM --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="mdi mdi-information text-primary"></i>
                                Basic Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                                                        <div class="mb-4 text-center">
                                <label for="iconInput" class="position-relative d-inline-block cursor-pointer"
                                    title="Upload Icon">
                                    {{-- Preview Image --}}
                                    <img id="iconPreview"
                                        src="{{ isset($productOption) && $productOption->icon ?  Storage::url($productOption->icon) : asset('assets/images/placeholder.svg') }}"
                                        class="rounded border shadow-sm object-fit-cover" width="120" height="120"
                                        alt="Icon Preview">

                                    {{-- Camera Icon Overlay --}}
                                    <div
                                        class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm border">
                                        <i class="mdi mdi-camera text-primary"></i>
                                    </div>

                                    {{-- Hidden File Input --}}
                                    <input type="file" name="icon" id="iconInput" class="d-none" accept="image/*"
                                        onchange="previewImage(event)">
                                </label>
                                <div class="small text-muted mt-2">Click image to upload</div>
                            </div>
                            <div class="row g-4">

                                {{-- CATEGORY --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-shape text-muted"></i>
                                        </span>
                                        <select name="category_id" id="categorySelect" class="form-select border-start-0" required>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}"
                                                    {{ $productOption->category_id == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">Please select a category.</div>
                                </div>

                                {{-- SUBCATEGORY --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Subcategory <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-tag text-muted"></i>
                                        </span>
                                        <select name="subcategory_id" id="subcategorySelect" class="form-select border-start-0" required>
                                            @foreach ($subcategories as $sub)
                                                <option value="{{ $sub->id }}"
                                                    {{ $productOption->subcategory_id == $sub->id ? 'selected' : '' }}>
                                                    {{ $sub->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">Please select a subcategory.</div>
                                </div>

                                {{-- OPTION NAME --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Option Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-format-text text-muted"></i>
                                        </span>
                                        <input type="text" name="option_name" class="form-control border-start-0"
                                            value="{{ old('option_name', $productOption->option_name) }}" 
                                            placeholder="Enter option name" required>
                                    </div>
                                    <div class="invalid-feedback">Option name is required.</div>
                                </div>

                                {{-- SKU --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        SKU <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-barcode text-muted"></i>
                                        </span>
                                        <input type="text" name="sku" class="form-control border-start-0"
                                            value="{{ old('sku', $productOption->sku) }}" 
                                            placeholder="Enter SKU code" required>
                                    </div>
                                    <div class="invalid-feedback">SKU is required.</div>
                                </div>

                                {{-- UNIT --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">
                                        Unit <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-cube text-muted"></i>
                                        </span>
                                        <select name="unit" class="form-select border-start-0" required>
                                            <option value="">Select Unit</option>
                                            <option value="kg" {{ $productOption->unit == 'kg' ? 'selected' : '' }}>
                                                Kilogram (KG)
                                            </option>
                                            <option value="pcs" {{ $productOption->unit == 'pcs' ? 'selected' : '' }}>
                                                Pieces (PCS)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">Unit is required.</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-lg-4">
                    {{-- PRICING INFORMATION --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="mdi mdi-currency-usd text-success"></i>
                                Pricing Details
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">

                                {{-- TAX --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Tax Percentage (%)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-percent text-muted"></i>
                                        </span>
                                        <input type="number" step="0.01" name="tax_percent" 
                                               class="form-control border-start-0"
                                               value="{{ old('tax_percent', $productOption->tax_percent) }}" 
                                               placeholder="0.00">
                                    </div>
                                </div>

                                {{-- COST PRICE --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Cost Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" name="cost_price" 
                                               class="form-control border-start-0"
                                               value="{{ old('cost_price', $productOption->cost_price) }}" 
                                               placeholder="0.00">
                                    </div>
                                </div>

                                {{-- BASE PRICE --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Base Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" name="base_price" 
                                               class="form-control border-start-0"
                                               value="{{ old('base_price', $productOption->base_price) }}" 
                                               placeholder="0.00">
                                    </div>
                                </div>

                                {{-- MRP --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">MRP (Maximum Retail Price)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" name="mrp" 
                                               class="form-control border-start-0"
                                               value="{{ old('mrp', $productOption->mrp) }}" 
                                               placeholder="0.00">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS CARD --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg text-white">
                                    <i class="mdi mdi-content-save me-1"></i>
                                    Update Option
                                </button>
                                <a href="{{ route('warehouse.product-options.index') }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="mdi mdi-close-circle me-1"></i>
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    @include('warehouse.product-options._scripts')

</x-app-layout>