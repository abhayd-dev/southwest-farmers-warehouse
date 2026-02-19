<x-app-layout title="Create Purchase Order">
    <div class="container-fluid">
        <form method="POST" action="{{ route('warehouse.purchase-orders.store') }}" id="poForm">
            @csrf
            
            {{-- HEADER SECTION --}}
            <div class="bg-white border-bottom shadow-sm mb-4">
                <div class="py-3">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div class="d-flex flex-column gap-2">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="mdi mdi-home-outline"></i> Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('warehouse.purchase-orders.index') }}" class="text-decoration-none">Purchase Orders</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Create New</li>
                                </ol>
                            </nav>
                            <h4 class="fw-bold mb-0 text-dark">
                                <i class="mdi mdi-cart-plus text-success"></i> New Purchase Order
                            </h4>
                        </div>
                        <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                            <a href="{{ route('warehouse.purchase-orders.index') }}" class="btn btn-light border text-muted shadow-sm flex-fill flex-md-grow-0">Cancel</a>
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
                            <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-information-outline me-1"></i> Order Details</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-domain"></i></span>
                                    <select name="vendor_id" class="form-select border-start-0" required>
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
                                    <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Expected Delivery</label>
                                    <input type="date" name="expected_delivery_date" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="4" placeholder="Optional notes for this order..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Approval Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-email-outline"></i></span>
                                    <input 
                                        type="email" 
                                        name="approval_email" 
                                        class="form-control border-start-0" 
                                        placeholder="manager@example.com"
                                    >
                                </div>
                                <small class="text-muted">If provided, an approval email will be sent to this address</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Items Table --}}
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-format-list-bulleted me-1"></i> Order Items</h6>
                            <button type="button" class="btn btn-sm btn-primary shadow-sm" id="addRowBtn">
                                <i class="mdi mdi-plus-circle me-1"></i> Add Item
                            </button>
                        </div>
                        
                        <div class="card-body p-0">
                            {{-- IMPORTANT: table-responsive enables the scrollbar --}}
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-5 text-nowrap" id="itemsTable">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            {{-- Min-widths force the table to be wide enough to trigger scroll on mobile --}}
                                            <th style="min-width: 250px;">Product <span class="text-danger">*</span></th>
                                            <th style="min-width: 120px;">Quantity <span class="text-danger">*</span></th>
                                            <th style="min-width: 150px;">Unit Cost <span class="text-danger">*</span></th>
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
                                            <td colspan="2" class="fw-bold fs-5 text-success text-end px-3" id="grandTotalDisplay">$ 0.00</td>
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
        let rowIdx = 0;

        function addRow() {
            let options = '<option value="">Select Product</option>';
            products.forEach(p => {
                // Show Barcode first, then product name
                const barcode = p.barcode || 'NO-BARCODE';
                options += `<option value="${p.id}" data-cost="${p.cost_price}">${barcode} - ${p.product_name}</option>`;
            });

            const html = `
                <tr id="row-${rowIdx}">
                    <td>
                        <select name="items[${rowIdx}][product_id]" class="form-select product-select border-0 bg-light" onchange="updateCost(${rowIdx})" required>
                            ${options}
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
            rowIdx++;
            
            // Ensure empty state is hidden
            $('#emptyState').addClass('d-none');
        }

        window.updateCost = function(idx) {
            const select = $(`#row-${idx} .product-select`);
            const cost = select.find(':selected').data('cost');
            if(cost) {
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
                const val = parseFloat($(this).text().replace('$','')) || 0;
                total += val;
            });
            $('#grandTotalDisplay').text('$ ' + total.toFixed(2));
        }

        // Init
        document.getElementById('addRowBtn').addEventListener('click', addRow);
        addRow(); // Add one row by default
    </script>
    @endpush
</x-app-layout>