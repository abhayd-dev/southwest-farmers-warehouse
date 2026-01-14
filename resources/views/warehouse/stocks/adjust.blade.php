<x-app-layout title="Stock Adjustment">
    <div class="container-fluid">
        
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('warehouse.stocks.index') }}">Inventory</a></li>
                <li class="breadcrumb-item active">Stock Adjustment</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-warning bg-opacity-10 text-dark py-3">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-scale-balance me-2"></i> Adjust Stock Level</h5>
                    </div>
                    
                    <form method="POST" action="{{ route('warehouse.stocks.store-adjustment') }}" class="card-body p-4 needs-validation" novalidate>
                        @csrf
                        
                        {{-- Product --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Product</label>
                            <select name="product_id" id="productSelect" class="form-select form-select-lg" required>
                                <option value="">-- Choose Product --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-unit="{{ $p->unit }}">
                                        {{ $p->product_name }} (SKU: {{ $p->sku }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text" id="stockDisplay"></div>
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
                                <label class="form-label fw-bold">Reason</label>
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
                            <label class="form-label fw-bold">Quantity</label>
                            <div class="input-group">
                                <input type="number" name="quantity" class="form-control" step="0.01" required placeholder="0.00">
                                <span class="input-group-text bg-light" id="unitDisplay">Unit</span>
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Describe why this adjustment is being made..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark">Confirm Adjustment</button>
                            <a href="{{ route('warehouse.stocks.index') }}" class="btn btn-link text-muted">Cancel</a>
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
                            `<span class="text-primary fw-bold">Current Stock: ${d.current_stock} ${d.unit}</span>`;
                    });
            } else {
                document.getElementById('stockDisplay').innerHTML = '';
            }
        });
    </script>
    @endpush
</x-app-layout>