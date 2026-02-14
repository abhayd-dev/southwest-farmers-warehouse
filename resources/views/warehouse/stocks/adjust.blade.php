<x-app-layout title="Stock Adjustment">
    <div class="container-fluid">
        
        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    {{-- BREADCRUMB --}}
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                    <i class="mdi mdi-home-outline"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('warehouse.stocks.index') }}" class="text-decoration-none">
                                    Stock & Inventory
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Stock Adjustment
                            </li>
                        </ol>
                    </nav>
                    
                    {{-- TITLE --}}
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-scale-balance text-warning"></i> Adjust Stock Level
                    </h4>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-muted">Adjustment Details</h6>
                    </div>
                    
                    <form method="POST" action="{{ route('warehouse.stocks.store-adjustment') }}" class="card-body p-4 needs-validation" novalidate>
                        @csrf
                        
                        {{-- Product --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Product <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-package-variant"></i>
                                </span>
                                <select name="product_id" id="productSelect" class="form-select form-select-lg border-start-0" required>
                                    <option value="">-- Choose Product --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-unit="{{ $p->unit }}">
                                            {{ $p->product_name }} (SKU: {{ $p->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-text mt-2" id="stockDisplay"></div>
                        </div>

                        {{-- Action Type --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Action</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="action" id="actAdd" value="add" autocomplete="off">
                                    <label class="btn btn-outline-success" for="actAdd"><i class="mdi mdi-plus"></i> Add (+)</label>
                                    
                                    <input type="radio" class="btn-check" name="action" id="actSub" value="subtract" autocomplete="off" checked>
                                    <label class="btn btn-outline-danger" for="actSub"><i class="mdi mdi-minus"></i> Remove (-)</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
                                <select name="reason" class="form-select" required>
                                    <option value="damage">Damage / Expired</option>
                                    <option value="adjustment">Inventory Correction</option>
                                    <option value="theft">Theft / Loss</option>
                                    <option value="return">Customer Return (Add Only)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="quantity" class="form-control" step="0.01" required placeholder="0.00">
                                <span class="input-group-text bg-light fw-bold text-muted" id="unitDisplay">Unit</span>
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Describe why this adjustment is being made..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="mdi mdi-check-circle me-1"></i> Confirm Adjustment
                            </button>
                            <a href="{{ route('warehouse.stocks.index') }}" class="btn btn-warning shadow-sm">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('productSelect').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const unit = option.dataset.unit || 'Unit';
            document.getElementById('unitDisplay').textContent = unit;
            
            if(this.value) {
                fetch("{{ url('warehouse/stocks/product-details') }}/" + this.value)
                    .then(r => r.json())
                    .then(d => {
                        document.getElementById('stockDisplay').innerHTML = 
                            `<div class="alert alert-info py-2 px-3 mb-0 border-0 d-flex align-items-center">
                                <i class="mdi mdi-information-outline me-2 fs-5"></i>
                                <span>Current Stock: <strong class="text-dark">${d.current_stock} ${d.unit}</strong></span>
                             </div>`;
                    });
            } else {
                document.getElementById('stockDisplay').innerHTML = '';
            }
        });
    </script>
    @endpush
</x-app-layout>