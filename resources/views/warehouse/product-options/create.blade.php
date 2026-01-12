<x-app-layout title="Create Product Option">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.partials.breadcrumb', ['title' => 'Create Product Option'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-plus-circle text-success"></i>
                        Create New Product Option
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.product-options.store') }}" class="needs-validation" novalidate>
            @csrf

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
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
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
                                            <option value="">Select Subcategory</option>
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
                                               placeholder="Enter option name" required>
                                    </div>
                                    <div class="invalid-feedback">Please enter an option name.</div>
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
                                            <option value="kg">Kilogram (KG)</option>
                                            <option value="pcs">Pieces (PCS)</option>
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">Please select a unit.</div>
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
                                               class="form-control border-start-0" placeholder="0.00">
                                    </div>
                                </div>

                                {{-- COST PRICE --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Cost Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" name="cost_price" 
                                               class="form-control border-start-0" placeholder="0.00">
                                    </div>
                                </div>

                                {{-- BASE PRICE --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Base Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" name="base_price" 
                                               class="form-control border-start-0" placeholder="0.00">
                                    </div>
                                </div>

                                {{-- MRP --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">MRP (Maximum Retail Price)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">$</span>
                                        <input type="number" step="0.01" name="mrp" 
                                               class="form-control border-start-0" placeholder="0.00">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS CARD --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="mdi mdi-check-circle me-1"></i>
                                    Save Product Option
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