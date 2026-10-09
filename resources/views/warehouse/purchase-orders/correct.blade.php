<x-app-layout title="Correct Order">
    <div class="container-fluid">
        <form method="POST" action="{{ route('warehouse.purchase-orders.correct', $purchaseOrder->id) }}" id="correctForm">
            @csrf

            {{-- HEADER SECTION --}}
            <div class="bg-white border-bottom shadow-sm mb-4">
                <div class="py-3">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div class="d-flex flex-column gap-2">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                            class="text-decoration-none"><i class="mdi mdi-home-outline"></i>
                                            Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('warehouse.purchase-orders.show', $purchaseOrder->id) }}"
                                            class="text-decoration-none">{{ $purchaseOrder->po_number }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Correct Order</li>
                                </ol>
                            </nav>
                            <h4 class="fw-bold mb-0 text-dark">
                                <i class="mdi mdi-file-document-edit-outline text-warning"></i> Correct {{ $purchaseOrder->po_number }}
                            </h4>
                        </div>
                        <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                            <a href="{{ route('warehouse.purchase-orders.show', $purchaseOrder->id) }}"
                                class="btn btn-light border text-muted shadow-sm flex-fill flex-md-grow-0">Cancel</a>
                            <button type="submit" class="btn btn-warning shadow-sm flex-fill flex-md-grow-0">
                                <i class="mdi mdi-content-save me-1"></i> Save Correction
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                <i class="mdi mdi-alert-outline fs-5"></i>
                <div>
                    This order is already <strong>Completed</strong>; its stock has already been added to the
                    warehouse. Changing a line's <strong>Received Qty</strong> here adjusts warehouse stock by the
                    difference immediately on save. If that stock has since moved on to a store or a sale, the
                    correction will be refused rather than drive stock negative.
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-format-list-bulleted me-1"></i> Order Items</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-3">UPC</th>
                                            <th class="px-3">Product</th>
                                            <th class="text-center" style="min-width: 120px;">Ordered Qty</th>
                                            <th class="text-center" style="min-width: 120px;">Received Qty</th>
                                            <th class="text-center" style="min-width: 130px;">Unit Cost ($)</th>
                                            <th class="text-center" style="min-width: 110px;">Line Total ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchaseOrder->items as $item)
                                            <tr class="correct-row">
                                                <td class="px-3"><span class="badge bg-secondary">{{ $item->product->upc ?? 'N/A' }}</span></td>
                                                <td class="px-3">
                                                    <span class="fw-semibold text-dark">{{ $item->product->product_name ?? ('Product #' . $item->product_id) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" name="items[{{ $item->id }}][requested_quantity]"
                                                        class="form-control form-control-sm text-center correct-ordered-qty"
                                                        min="0" step="0.01" value="{{ old('items.' . $item->id . '.requested_quantity', $item->requested_quantity) }}" required>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" name="items[{{ $item->id }}][received_quantity]"
                                                        class="form-control form-control-sm text-center correct-received-qty"
                                                        min="0" step="0.01" value="{{ old('items.' . $item->id . '.received_quantity', $item->received_quantity) }}" required>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" name="items[{{ $item->id }}][unit_cost]"
                                                        class="form-control form-control-sm text-center correct-unit-cost"
                                                        min="0" step="0.01" value="{{ old('items.' . $item->id . '.unit_cost', $item->unit_cost) }}" required>
                                                </td>
                                                <td class="text-center fw-semibold correct-line-total">
                                                    {{ number_format($item->requested_quantity * $item->unit_cost, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-cash-multiple me-1"></i> Invoice & Landed Costs</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Vendor Invoice Number</label>
                                <input type="text" name="vendor_invoice_number" class="form-control"
                                    value="{{ old('vendor_invoice_number', $purchaseOrder->vendor_invoice_number) }}">
                            </div>
                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold">Duties ($)</label>
                                    <input type="number" name="duties" class="form-control correct-overhead" min="0" step="0.01"
                                        value="{{ old('duties', $purchaseOrder->duties) }}">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold">Shipping ($)</label>
                                    <input type="number" name="shipping_cost" class="form-control correct-overhead" min="0" step="0.01"
                                        value="{{ old('shipping_cost', $purchaseOrder->shipping_cost) }}">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold">Taxes ($)</label>
                                    <input type="number" name="taxes" class="form-control correct-overhead" min="0" step="0.01"
                                        value="{{ old('taxes', $purchaseOrder->taxes) }}">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold">Transportation ($)</label>
                                    <input type="number" name="transportation_cost" class="form-control correct-overhead" min="0" step="0.01"
                                        value="{{ old('transportation_cost', $purchaseOrder->transportation_cost) }}">
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label fw-semibold">Demurrage ($)</label>
                                    <input type="number" name="demurrage" class="form-control correct-overhead" min="0" step="0.01"
                                        value="{{ old('demurrage', $purchaseOrder->demurrage) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-note-text-outline me-1"></i> Reason for Correction</h6>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <textarea name="reason" class="form-control flex-grow-1" rows="4" placeholder="Why is this completed order being corrected? (required)" required>{{ old('reason') }}</textarea>
                            <small class="text-muted d-block mt-2">
                                Admins are notified of every correction made here, along with this reason.
                            </small>
                            <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                                <span class="text-muted">New Total Amount:</span>
                                <span class="fw-bold text-success fs-4" id="correctGrandTotal">
                                    ${{ number_format($purchaseOrder->items->sum(fn ($i) => $i->requested_quantity * $i->unit_cost) + $purchaseOrder->duties + $purchaseOrder->shipping_cost + $purchaseOrder->taxes, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function correctRecalculate() {
            let grandTotal = 0;
            document.querySelectorAll('.correct-row').forEach(function (row) {
                const orderedQty = parseFloat(row.querySelector('.correct-ordered-qty').value) || 0;
                const unitCost = parseFloat(row.querySelector('.correct-unit-cost').value) || 0;
                const lineTotal = orderedQty * unitCost;
                row.querySelector('.correct-line-total').innerText = lineTotal.toFixed(2);
                grandTotal += lineTotal;
            });
            ['duties', 'shipping_cost', 'taxes'].forEach(function (name) {
                grandTotal += parseFloat(document.querySelector('[name="' + name + '"]').value) || 0;
            });
            const totalEl = document.getElementById('correctGrandTotal');
            if (totalEl) totalEl.innerText = '$' + grandTotal.toFixed(2);
        }

        document.querySelectorAll('.correct-ordered-qty, .correct-unit-cost, .correct-overhead').forEach(function (input) {
            input.addEventListener('input', correctRecalculate);
        });
        correctRecalculate();

        const correctForm = document.getElementById('correctForm');
        if (correctForm) {
            correctForm.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Save this correction?',
                    text: 'This changes the recorded order and, where Received Qty changed, warehouse stock immediately.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save correction',
                    cancelButtonText: 'Go back',
                }).then((result) => {
                    if (result.isConfirmed) {
                        correctForm.submit();
                    }
                });
            });
        }
    </script>
    @endpush
</x-app-layout>
