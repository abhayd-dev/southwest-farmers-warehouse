<x-app-layout title="Create Purchase Order">
    <div class="container-fluid">
        <form method="POST" action="{{ route('warehouse.purchase-orders.store') }}" id="poForm">
            @csrf
            
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold"><i class="mdi mdi-plus-circle text-success"></i> New Purchase Order</h4>
                <div>
                    <a href="{{ route('warehouse.purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success px-4"><i class="mdi mdi-content-save me-1"></i> Save Draft</button>
                </div>
            </div>

            <div class="row">
                {{-- Left: Vendor & Dates --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light fw-bold">Order Details</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-select" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order Date <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Expected Delivery</label>
                                <input type="date" name="expected_delivery_date" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Items Table --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Items</span>
                            <button type="button" class="btn btn-sm btn-primary" id="addRowBtn">
                                <i class="mdi mdi-plus"></i> Add Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" id="itemsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 40%;">Product</th>
                                            <th style="width: 20%;">Quantity</th>
                                            <th style="width: 20%;">Unit Cost</th>
                                            <th style="width: 15%;">Total</th>
                                            <th style="width: 5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Rows added via JS --}}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                            <td colspan="2" class="fw-bold fs-5" id="grandTotalDisplay">₹ 0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
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
                options += `<option value="${p.id}" data-cost="${p.cost_price}">${p.product_name} (${p.sku})</option>`;
            });

            const html = `
                <tr id="row-${rowIdx}">
                    <td>
                        <select name="items[${rowIdx}][product_id]" class="form-select product-select" onchange="updateCost(${rowIdx})" required>
                            ${options}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][quantity]" class="form-control qty-input" min="1" value="1" oninput="calculateRow(${rowIdx})" required>
                    </td>
                    <td>
                        <input type="number" name="items[${rowIdx}][cost]" class="form-control cost-input" step="0.01" min="0" oninput="calculateRow(${rowIdx})" required>
                    </td>
                    <td class="align-middle fw-semibold text-end row-total">0.00</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${rowIdx})"><i class="mdi mdi-delete"></i></button>
                    </td>
                </tr>
            `;
            $('#itemsTable tbody').append(html);
            rowIdx++;
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
            $(`#row-${idx} .row-total`).text(total.toFixed(2));
            calculateGrandTotal();
        }

        window.removeRow = function(idx) {
            $(`#row-${idx}`).remove();
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            $('.row-total').each(function() {
                total += parseFloat($(this).text()) || 0;
            });
            $('#grandTotalDisplay').text('₹ ' + total.toFixed(2));
        }

        // Init
        document.getElementById('addRowBtn').addEventListener('click', addRow);
        addRow(); // Add one row by default
    </script>
    @endpush
</x-app-layout>