<x-app-layout title="Create Purchase Order">
    <div class="container-fluid">
        <form method="POST" action="{{ route('warehouse.purchase-orders.store') }}" id="poForm">
            @csrf

            {{-- HEADER SECTION --}}
            <div class="bg-white border-bottom shadow-sm mb-4">
                <div class="py-3">
                    <div
                        class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div class="d-flex flex-column gap-2">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                            class="text-decoration-none"><i class="mdi mdi-home-outline"></i>
                                            Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('warehouse.purchase-orders.index') }}"
                                            class="text-decoration-none">Purchase Orders</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Create New</li>
                                </ol>
                            </nav>
                            <h4 class="fw-bold mb-0 text-dark">
                                <i class="mdi mdi-cart-plus text-success"></i> New Purchase Order
                            </h4>
                        </div>
                        <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                            <a href="{{ route('warehouse.purchase-orders.index') }}"
                                class="btn btn-light border text-muted shadow-sm flex-fill flex-md-grow-0">Cancel</a>
                            <button type="submit" class="btn btn-success shadow-sm flex-fill flex-md-grow-0">
                                <i class="mdi mdi-content-save me-1"></i> Save Draft
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- LEFT: Vendor & Basic Info --}}
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-information-outline me-1"></i> Order
                                Details</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="mdi mdi-domain"></i></span>
                                    <select name="vendor_id" class="form-select border-start-0" required>
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Order Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="order_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Expected Delivery</label>
                                    <input type="date" name="expected_delivery_date" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vendor Instructions</label>
                                <textarea name="vendor_notes" class="form-control" rows="4" placeholder="Optional instructions for vendor..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Approval Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="mdi mdi-email-outline"></i></span>
                                    <input type="email" name="approval_email" class="form-control border-start-0"
                                        placeholder="manager@example.com">
                                </div>
                                <small class="text-muted">If provided, an approval email will be sent to this
                                    address</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Approver Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="mdi mdi-phone-outline"></i></span>
                                    <input type="tel" name="approver_phone" class="form-control border-start-0"
                                        placeholder="+1 234 567 8900">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Items Table --}}
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-format-list-bulleted me-1"></i> Order Items</h6>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" id="addRowBtn">
                                    <i class="mdi mdi-plus-circle me-1"></i> Add Item
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small text-muted fw-bold mb-1">Department</label>
                                    <select id="filterDepartment" class="form-select form-select-sm shadow-none" onchange="onDepartmentChange()">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted fw-bold mb-1">Category</label>
                                    <select id="filterCategory" class="form-select form-select-sm shadow-none" onchange="onCategoryChange()">
                                        <option value="">All Categories</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small text-muted fw-bold mb-1">Subcategory</label>
                                    <select id="filterSubcategory" class="form-select form-select-sm shadow-none" onchange="filterProducts()">
                                        <option value="">All Subcategories</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            {{-- IMPORTANT: table-responsive enables the scrollbar --}}
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-5 text-nowrap" id="itemsTable">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            {{-- Min-widths force the table to be wide enough to trigger scroll on mobile --}}
                                            <th style="min-width: 250px;">Product <span class="text-danger">*</span>
                                            </th>
                                            <th style="min-width: 120px;">Quantity <span class="text-danger">*</span>
                                            </th>
                                            <th style="min-width: 150px;">Unit Cost <span class="text-danger">*</span>
                                            </th>
                                            <th style="min-width: 120px;" class="text-end">Total</th>
                                            <th style="min-width: 50px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        {{-- Rows added via JS --}}
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold text-dark">Grand Total:</td>
                                            <td colspan="2" class="fw-bold fs-5 text-success text-end px-3"
                                                id="grandTotalDisplay">$ 0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Empty State --}}
                            <div id="emptyState" class="text-center py-5 d-none">
                                <i class="mdi mdi-cart-off text-muted opacity-25" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No items added yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const products = @json($products);
            const allCategories = @json($categories);
            const allSubcategories = @json($subcategories);
            let rowIdx = 0;
            let productOptionsHtml = '<option value="">Select Product</option>';

            // Category has no direct department_id in the schema, so "which categories
            // belong to this department" is derived from the products actually in it.
            function categoryIdsForDepartment(deptId) {
                if (!deptId) return null; // null = no restriction
                return new Set(products.filter(p => p.department_id == deptId).map(p => p.category_id));
            }

            function rebuildCategoryOptions(deptId, keepValue) {
                const allowedIds = categoryIdsForDepartment(deptId);
                const $sel = $('#filterCategory');
                let html = '<option value="">All Categories</option>';
                allCategories.forEach(c => {
                    if (!allowedIds || allowedIds.has(c.id)) {
                        html += `<option value="${c.id}">${c.name}</option>`;
                    }
                });
                $sel.html(html);
                if (keepValue && (!allowedIds || allowedIds.has(parseInt(keepValue)))) {
                    $sel.val(keepValue);
                }
            }

            function rebuildSubcategoryOptions(catId, deptId, keepValue) {
                const $sel = $('#filterSubcategory');
                let html = '<option value="">All Subcategories</option>';
                let list = allSubcategories;
                if (catId) {
                    list = list.filter(s => s.category_id == catId);
                } else if (deptId) {
                    const allowedSubcatIds = new Set(products.filter(p => p.department_id == deptId).map(p => p.subcategory_id));
                    list = list.filter(s => allowedSubcatIds.has(s.id));
                }
                list.forEach(s => { html += `<option value="${s.id}">${s.name}</option>`; });
                $sel.html(html);
                if (keepValue && list.some(s => s.id == keepValue)) {
                    $sel.val(keepValue);
                }
            }

            window.onDepartmentChange = function() {
                // Selecting a department narrows Category, which narrows Subcategory —
                // both downstream filters reset since they may no longer apply.
                rebuildCategoryOptions($('#filterDepartment').val(), null);
                rebuildSubcategoryOptions(null, $('#filterDepartment').val(), null);
                filterProducts();
            }

            window.onCategoryChange = function() {
                rebuildSubcategoryOptions($('#filterCategory').val(), $('#filterDepartment').val(), null);
                filterProducts();
            }

            // Per-row memory of which filters were active when that row's product was
            // chosen, so switching filters for the NEXT row never disturbs earlier rows,
            // and clicking back into an earlier row restores the filters that found it.
            function rememberRowFilters(idx) {
                const $row = $(`#row-${idx}`);
                const productId = $row.find('.product-select').val();
                let dept = $('#filterDepartment').val();
                let cat = $('#filterCategory').val();
                let subcat = $('#filterSubcategory').val();

                // No filters were used to find this product — fall back to the
                // product's own department/category/subcategory so "click back"
                // still has something correct to restore.
                if (!dept && !cat && !subcat && productId) {
                    const p = products.find(prod => prod.id == productId);
                    if (p) {
                        dept = p.department_id || '';
                        cat = p.category_id || '';
                        subcat = p.subcategory_id || '';
                    }
                }

                $row.attr('data-dept', dept || '');
                $row.attr('data-cat', cat || '');
                $row.attr('data-subcat', subcat || '');
            }

            window.restoreRowFilters = function(idx) {
                const $row = $(`#row-${idx}`);
                const dept = $row.attr('data-dept') || '';
                const cat = $row.attr('data-cat') || '';
                const subcat = $row.attr('data-subcat') || '';

                $('#filterDepartment').val(dept);
                rebuildCategoryOptions(dept, cat);
                rebuildSubcategoryOptions($('#filterCategory').val(), dept, subcat);
                filterProducts();
            }

            function filterProducts() {
                const deptId = document.getElementById('filterDepartment').value;
                const catId = document.getElementById('filterCategory').value;
                const subcatId = document.getElementById('filterSubcategory').value;

                let filteredProducts = products;
                if (deptId) filteredProducts = filteredProducts.filter(p => p.department_id == deptId);
                if (catId) filteredProducts = filteredProducts.filter(p => p.category_id == catId);
                if (subcatId) filteredProducts = filteredProducts.filter(p => p.subcategory_id == subcatId);

                productOptionsHtml = '<option value="">Select Product</option>';
                filteredProducts.forEach(p => {
                    const barcode = p.barcode || 'NO-BARCODE';
                    productOptionsHtml += `<option value="${p.id}" data-cost="${p.cost_price}">${barcode} - ${p.product_name}</option>`;
                });

                // Update all existing dropdowns, but never drop a row's already-selected
                // product just because a filter no longer matches it — the filters are
                // for finding a NEW product, not for un-choosing one already on the order.
                $('.product-select').each(function() {
                    const currentVal = $(this).val();
                    let optionsForThisRow = productOptionsHtml;

                    if (currentVal && !filteredProducts.some(p => p.id == currentVal)) {
                        const selectedProduct = products.find(p => p.id == currentVal);
                        if (selectedProduct) {
                            const barcode = selectedProduct.barcode || 'NO-BARCODE';
                            optionsForThisRow += `<option value="${selectedProduct.id}" data-cost="${selectedProduct.cost_price}">${barcode} - ${selectedProduct.product_name}</option>`;
                        }
                    }

                    $(this).html(optionsForThisRow);
                    if (currentVal) {
                        $(this).val(currentVal);
                    }
                    // Force Select2's own rendered display to resync immediately —
                    // otherwise it can take a second filter change before it visually updates.
                    $(this).trigger('change.select2');
                });
            }

            // Initial build
            rebuildCategoryOptions(null, null);
            rebuildSubcategoryOptions(null, null, null);
            filterProducts();

            function addRow() {
                const html = `
                <tr id="row-${rowIdx}">
                    <td>
                        <select name="items[${rowIdx}][product_id]" class="form-select product-select border-0 bg-light" required>
                            ${productOptionsHtml}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][quantity]" class="form-control qty-input text-center" min="1" value="1" oninput="calculateRow(${rowIdx})" required style="min-width: 80px;">
                    </td>
                    <td>
                        <div class="input-group input-group-sm" style="min-width: 120px;">
                            <span class="input-group-text bg-white border-end-0">$</span>
                            <input type="number" name="items[${rowIdx}][cost]" class="form-control cost-input border-start-0" step="0.01" min="0" oninput="calculateRow(${rowIdx})" required>
                        </div>
                    </td>
                    <td class="align-middle fw-bold text-end row-total px-3">$0.00</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(${rowIdx})" title="Remove Item">
                            <i class="mdi mdi-trash-can fs-5"></i>
                        </button>
                    </td>
                </tr>
            `;
                $('#itemsTable tbody').append(html);

                // Re-initialize Select2 for this newly added row's select
                const thisRowIdx = rowIdx;
                $(`#row-${thisRowIdx} .product-select`).select2({
                    theme: 'bootstrap-5',
                    width: 'style',
                    placeholder: 'Select Product',
                    allowClear: true
                }).on('select2:select', function() {
                    updateCost(thisRowIdx);
                }).on('select2:clear', function() {
                    $(`#row-${thisRowIdx} .cost-input`).val('');
                    calculateRow(thisRowIdx);
                }).on('select2:opening', function() {
                    // Clicking back into this row restores the filters that found its product.
                    restoreRowFilters(thisRowIdx);
                });

                rowIdx++;

                // Ensure empty state is hidden
                $('#emptyState').addClass('d-none');
            }

            window.updateCost = function(idx) {
                const select = $(`#row-${idx} .product-select`);
                const cost = select.find(':selected').data('cost');
                rememberRowFilters(idx);
                if (cost !== undefined && cost !== null && cost !== '') {
                    $(`#row-${idx} .cost-input`).val(cost);
                }
                calculateRow(idx);
            }

            window.calculateRow = function(idx) {
                const qty = parseFloat($(`#row-${idx} .qty-input`).val()) || 0;
                const cost = parseFloat($(`#row-${idx} .cost-input`).val()) || 0;
                const total = qty * cost;
                $(`#row-${idx} .row-total`).text('$' + total.toFixed(2));
                calculateGrandTotal();
            }

            window.removeRow = function(idx) {
                $(`#row-${idx}`).remove();
                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                let total = 0;
                $('.row-total').each(function() {
                    // Remove $ and parse
                    const val = parseFloat($(this).text().replace('$', '')) || 0;
                    total += val;
                });
                $('#grandTotalDisplay').text('$ ' + total.toFixed(2));
            }

            // Init
            document.getElementById('addRowBtn').addEventListener('click', function() {
                // New row for a new product: keep the department filter, but
                // category/subcategory reset to "All" so the new pick isn't
                // accidentally narrowed by whatever the previous row was using.
                const dept = $('#filterDepartment').val();
                rebuildCategoryOptions(dept, null);
                rebuildSubcategoryOptions(null, dept, null);
                filterProducts();
                addRow();
            });

            @if (session('prefilled_items'))
                const prefilled = @json(session('prefilled_items'));
                prefilled.forEach(item => {
                    // Find product details
                    const p = products.find(prod => prod.id == item.product_id);
                    if (p) {
                        addRow();
                        const lastRowIdx = rowIdx - 1;
                        const select = $(`#row-${lastRowIdx} .product-select`);
                        select.val(p.id).trigger('change.select2');
                        rememberRowFilters(lastRowIdx);
                        if (item.cost !== undefined && item.cost !== null) {
                            $(`#row-${lastRowIdx} .cost-input`).val(item.cost);
                        } else if (p.cost_price) {
                            $(`#row-${lastRowIdx} .cost-input`).val(p.cost_price);
                        }
                        $(`#row-${lastRowIdx} .qty-input`).val(item.quantity);
                        calculateRow(lastRowIdx);
                    }
                });
            @else
                addRow(); // Add one row by default
            @endif
        </script>
    @endpush
</x-app-layout>
