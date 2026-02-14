<x-app-layout title="Stock In">
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
                                Stock In (Purchase)
                            </li>
                        </ol>
                    </nav>
                    
                    {{-- TITLE --}}
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-dolly text-success"></i> Receive New Stock
                    </h4>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-muted">Purchase Entry Details</h6>
                    </div>
                    
                    <form method="POST" action="{{ route('warehouse.stocks.store') }}" class="card-body p-4 needs-validation" novalidate>
                        @csrf
                        
                        {{-- 1. PRODUCT SELECTION --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Product <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="mdi mdi-cube-outline"></i>
                                </span>
                                <select name="product_id" id="productSelect" class="form-select form-select-lg border-start-0" required>
                                    <option value="">-- Choose Product --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->product_name }} (SKU: {{ $p->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-text mt-2" id="currentStockDisplay"></div>
                        </div>

                        <div class="row g-4">
                            
                            {{-- 2. QUANTITY & UNIT LOGIC --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantityInput" class="form-control" step="0.01" required placeholder="Enter Qty">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Unit Type <span class="text-danger">*</span></label>
                                <select name="unit_type" id="unitTypeSelect" class="form-select" required>
                                    <option value="base">Base Unit (Default)</option>
                                    {{-- JS will append Purchase Unit option here if available --}}
                                </select>
                                <div class="form-text text-success fw-bold" id="conversionDisplay"></div>
                            </div>

                            <div class="col-12 my-2">
                                <hr class="text-muted opacity-25">
                            </div>

                            {{-- 3. BATCH & EXPIRY (Dynamic Section) --}}
                            <div id="batchSection" class="row g-3 d-none mt-0">
                                <div class="col-12">
                                    <div class="alert alert-info py-2 px-3 small border-0 d-flex align-items-center">
                                        <i class="mdi mdi-information-outline me-2 fs-5"></i> 
                                        <span>This product has <strong>Batch Tracking</strong> enabled. Please fill details.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Batch Number <span class="text-danger">*</span></label>
                                    <input type="text" name="batch_number" class="form-control" placeholder="e.g. B-2026-01">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Expiry Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expiry_date" class="form-control">
                                </div>
                            </div>

                            {{-- 4. COSTING --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cost Price (Per Unit)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" name="cost_price" class="form-control" step="0.01" placeholder="Override default cost">
                                </div>
                                <div class="form-text small">Leave blank to use default product cost.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Remarks</label>
                                <input type="text" name="remarks" class="form-control" placeholder="e.g. PO #1234, Vendor Name">
                            </div>

                        </div>

                        <div class="d-grid gap-2 mt-4 pt-2">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i class="mdi mdi-check-circle me-1"></i> Add to Inventory
                            </button>
                            <a href="{{ route('warehouse.stocks.index') }}" class="btn btn-warning btn-lg shadow-sm">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- DYNAMIC SCRIPT --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('productSelect');
            const unitTypeSelect = document.getElementById('unitTypeSelect');
            const batchSection = document.getElementById('batchSection');
            const conversionDisplay = document.getElementById('conversionDisplay');
            const currentStockDisplay = document.getElementById('currentStockDisplay');
            const qtyInput = document.getElementById('quantityInput');

            let currentProduct = null;

            productSelect.addEventListener('change', function() {
                const productId = this.value;
                if (!productId) {
                    resetForm();
                    return;
                }

                // Fetch Product Details via AJAX
                fetch("{{ url('warehouse/stocks/product-details') }}/" + productId)
                    .then(res => res.json())
                    .then(data => {
                        currentProduct = data;
                        updateFormUI(data);
                    })
                    .catch(err => console.error('Error fetching product details:', err));
            });

            function updateFormUI(data) {
                // 1. Show Current Stock
                currentStockDisplay.innerHTML = `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                    Current Stock: <strong>${data.current_stock} ${data.unit}</strong>
                                                 </span>`;

                // 2. Handle Units
                unitTypeSelect.innerHTML = `<option value="base">Base Unit (${data.unit})</option>`;
                
                if (data.purchase_unit && data.conversion_factor > 1) {
                    unitTypeSelect.innerHTML += `<option value="purchase">${data.purchase_unit} (x${data.conversion_factor} ${data.unit})</option>`;
                }
                
                // Select 'purchase' by default if available
                if (data.purchase_unit) unitTypeSelect.value = 'purchase';

                // 3. Handle Batch Section
                if (data.is_batch_active) {
                    batchSection.classList.remove('d-none');
                    batchSection.querySelectorAll('input').forEach(i => i.setAttribute('required', 'required'));
                } else {
                    batchSection.classList.add('d-none');
                    batchSection.querySelectorAll('input').forEach(i => i.removeAttribute('required'));
                }

                updateConversionText();
            }

            function updateConversionText() {
                if (!currentProduct) return;
                
                const qty = parseFloat(qtyInput.value) || 0;
                const type = unitTypeSelect.value;
                
                if (type === 'purchase' && currentProduct.conversion_factor > 1) {
                    const total = qty * currentProduct.conversion_factor;
                    conversionDisplay.textContent = `Total adding: ${total} ${currentProduct.unit}`;
                } else {
                    conversionDisplay.textContent = `Total adding: ${qty} ${currentProduct.unit}`;
                }
            }

            // Listeners for calc updates
            qtyInput.addEventListener('input', updateConversionText);
            unitTypeSelect.addEventListener('change', updateConversionText);

            function resetForm() {
                currentProduct = null;
                unitTypeSelect.innerHTML = '<option value="base">Base Unit</option>';
                batchSection.classList.add('d-none');
                conversionDisplay.textContent = '';
                currentStockDisplay.textContent = '';
            }
        });
    </script>
    @endpush
</x-app-layout>