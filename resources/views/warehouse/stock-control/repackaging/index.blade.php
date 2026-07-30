<x-app-layout title="Warehouse Repackaging">
    <div class="container-fluid">
        <form method="POST" action="{{ route('warehouse.stock-control.repackaging.store') }}" id="repackagingForm">
            @csrf

            {{-- HEADER SECTION --}}
            <div class="bg-white border-bottom shadow-sm mb-4">
                <div class="py-3">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div class="d-flex flex-column gap-2">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                            <i class="mdi mdi-home-outline"></i> Dashboard
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('warehouse.stock-control.overview') }}" class="text-decoration-none">Stock Control</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Warehouse Repackaging</li>
                                </ol>
                            </nav>
                            <h4 class="fw-bold mb-0 text-dark">
                                <i class="mdi mdi-package-variant-closed text-primary me-2"></i> Warehouse Repackaging
                            </h4>
                        </div>
                        <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                            <a href="{{ route('warehouse.stock-control.overview') }}" class="btn btn-light border text-muted shadow-sm">Cancel</a>
                            <button type="submit" class="btn btn-success shadow-sm">
                                <i class="mdi mdi-check-all me-1"></i> Confirm Repackaging Transfer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ALERTS --}}
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show">
                    <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 alert-dismissible fade show">
                    <i class="mdi mdi-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4">
                {{-- LEFT CARD: Repackaging Details & Source Product --}}
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="mdi mdi-information-outline me-1 text-primary"></i> Repackaging Details
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date / Time <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                                <select class="form-select shadow-none">
                                    <option value="">GROCERY / RE-PACKAGE GOOD DRY GOODS</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category (The Re-packed Items Category) <span class="text-danger">*</span></label>
                                <select class="form-select shadow-none">
                                    <option value="">FARMERS PACKAGED</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">
                                    Product that is getting repackaged <span class="text-danger">*</span>
                                </label>
                                <select name="source_product_id" id="sourceProductSelect" class="form-select border-primary" required>
                                    <option value="">-- Select Bulk Product --</option>
                                    @foreach ($products as $prod)
                                        <option value="{{ $prod->id }}" {{ old('source_product_id') == $prod->id ? 'selected' : '' }}>
                                            {{ $prod->product_name }} ({{ $prod->upc ?? 'No UPC' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Selection automatically pre-fills associated repackaged sizes below.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Source Quantity to Deduct <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="source_quantity" class="form-control" value="{{ old('source_quantity', 1) }}" placeholder="e.g. 1 or 50" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Transfer Description / Remarks</label>
                                <input type="text" name="remarks" class="form-control bg-light" value="{{ old('remarks', 'repackaging transfer') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT CARD: Repackaged Output Items Table --}}
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="mdi mdi-format-list-bulleted me-1 text-primary"></i> Products That Are Repackaged
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="addItemBtn">
                                <i class="mdi mdi-plus me-1"></i> Add Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="repackagedItemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 55%" class="ps-4">Product (Search by UPC / Name) <span class="text-danger">*</span></th>
                                            <th style="width: 30%">Quantity (QTY) <span class="text-danger">*</span></th>
                                            <th style="width: 15%" class="text-end pe-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="repackagedItemsBody">
                                        {{-- Dynamic rows will be inserted here --}}
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-4 text-center text-muted border-top bg-light bg-opacity-25" id="emptyState">
                                <i class="mdi mdi-package-variant fs-1 opacity-50 d-block mb-1"></i>
                                Select a bulk product on the left to auto-fill repackaged items, or click <strong>+ Add Item</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sourceSelect = document.getElementById('sourceProductSelect');
                const itemsBody = document.getElementById('repackagedItemsBody');
                const emptyState = document.getElementById('emptyState');
                const addItemBtn = document.getElementById('addItemBtn');

                let rowCounter = 0;

                // Products list for select dropdown
                const allProducts = @json($products);

                function createRow(selectedProductId = '', qty = 1) {
                    rowCounter++;
                    emptyState.style.display = 'none';

                    const tr = document.createElement('tr');
                    tr.id = `row-${rowCounter}`;

                    let optionsHtml = '<option value="">-- Select Repackaged Product --</option>';
                    allProducts.forEach(p => {
                        const isSelected = p.id == selectedProductId ? 'selected' : '';
                        optionsHtml += `<option value="${p.id}" ${isSelected}>${p.product_name} (${p.upc || 'No UPC'})</option>`;
                    });

                    tr.innerHTML = `
                        <td class="ps-4">
                            <select name="items[${rowCounter}][product_id]" class="form-select item-product-select shadow-none" required>
                                ${optionsHtml}
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0.01" name="items[${rowCounter}][quantity]" class="form-control shadow-none" value="${qty}" required>
                        </td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" data-row-id="row-${rowCounter}">
                                <i class="mdi mdi-trash-can-outline"></i>
                            </button>
                        </td>
                    `;

                    itemsBody.appendChild(tr);

                    tr.querySelector('.remove-row-btn').addEventListener('click', function() {
                        tr.remove();
                        if (itemsBody.children.length === 0) {
                            emptyState.style.display = 'block';
                        }
                    });
                }

                // Add empty row manually
                addItemBtn.addEventListener('click', function() {
                    createRow();
                });

                // Auto-association AJAX trigger
                sourceSelect.addEventListener('change', function() {
                    const productId = this.value;
                    if (!productId) return;

                    itemsBody.innerHTML = '';
                    emptyState.style.display = 'block';

                    fetch(`/warehouse/stock-control/repackaging/associated/${productId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success' && data.products.length > 0) {
                                data.products.forEach(prod => {
                                    createRow(prod.id, 10);
                                });
                            } else {
                                // If no auto-associated items, create 1 empty row
                                createRow();
                            }
                        })
                        .catch(err => {
                            console.error('Error fetching associated products:', err);
                            createRow();
                        });
                });
            });
        </script>
    @endpush
</x-app-layout>
