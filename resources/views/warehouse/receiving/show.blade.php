@extends('layouts.app')

@section('title', 'Receive Purchase Order #' . $purchaseOrder->po_number)

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col-12 d-flex justify-content-between">
                <h4 class="mb-0 font-weight-bold text-dark">
                    <i class="mdi mdi-truck-check text-primary me-2"></i> Receive Order: {{ $purchaseOrder->po_number }}
                </h4>
                <div class="d-flex gap-2">
                    @if ($purchaseOrder->status == 'completed')
                        <a href="{{ route('warehouse.receiving.receipt', $purchaseOrder->id) }}" target="_blank"
                            class="btn btn-outline-primary shadow-sm">
                            <i class="mdi mdi-printer me-1"></i> Print Receiving Order
                        </a>
                    @endif
                    <a href="{{ route('warehouse.receiving.index') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back to Received Orders
                    </a>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-12">
                {{-- SUMMARY CARDS --}}
                <div class="row g-4 mb-4">
                    {{-- Vendor Info --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 position-relative rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 0.5px;">
                                            Vendor Details</h6>
                                        <h5 class="fw-bold text-dark mb-0">{{ $purchaseOrder->vendor->name }}</h5>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <i class="mdi mdi-domain fs-4"></i>
                                    </div>
                                </div>
                                <hr class="text-muted opacity-25 my-3">
                                <div class="d-flex flex-column gap-2 text-dark">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-email-outline text-muted me-2 fs-5"></i>
                                        <span>{{ $purchaseOrder->vendor->email ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-phone-outline text-muted me-2 fs-5"></i>
                                        <span>{{ $purchaseOrder->vendor->phone ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Info --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 position-relative rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 0.5px;">
                                            Order Summary</h6>
                                        <h5 class="fw-bold text-dark mb-0">Total:
                                            ${{ number_format($purchaseOrder->total_amount, 2) }}</h5>
                                    </div>
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <i class="mdi mdi-file-document-outline fs-4"></i>
                                    </div>
                                </div>
                                <hr class="text-muted opacity-25 my-3">
                                <div class="d-flex flex-column gap-2 text-dark">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted"><i class="mdi mdi-calendar me-1"></i> Order Date:</span>
                                        <span
                                            class="fw-medium">{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d M Y') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted"><i class="mdi mdi-account me-1"></i> Requested By:</span>
                                        <span class="fw-medium">{{ $purchaseOrder->creator->name ?? 'System' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Info --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 position-relative rounded-4 overflow-hidden">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 0.5px;">
                                            Receiving Progress</h6>
                                        @php
                                            $badges = [
                                                'ordered' => 'info',
                                                'partial' => 'warning',
                                                'completed' => 'success',
                                            ];
                                            $displayStatus = strtoupper($purchaseOrder->status);
                                            if ($purchaseOrder->status === 'partial') {
                                                $displayStatus = 'IN TRANSIT';
                                            }
                                        @endphp
                                        <h5 class="fw-bold text-dark mb-0">
                                            <span
                                                class="badge bg-{{ $badges[$purchaseOrder->status] ?? 'secondary' }} rounded-pill px-3 py-1">
                                                {{ $displayStatus }}
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <i class="mdi mdi-truck-delivery fs-4"></i>
                                    </div>
                                </div>
                                <hr class="text-muted opacity-25 my-3">
                                <div class="mt-2 text-dark">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold">Progress</span>
                                        @if ($purchaseOrder->progress < 100 && $purchaseOrder->status === 'completed')
                                            <span class="small fw-bold text-primary">PARTIAL COMPLETED</span>
                                        @else
                                            <span class="small fw-bold text-primary">{{ $purchaseOrder->progress }}%</span>
                                        @endif
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $purchaseOrder->progress }}%;"
                                            aria-valuenow="{{ $purchaseOrder->progress }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RECEIVE SECTION --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receive_po'))
                    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-primary mb-0"><i class="mdi mdi-truck-check me-2"></i> Receive Incoming
                                Stock</h5>
                            <button type="button" id="receiveAllBtn" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="mdi mdi-check-all me-1"></i> Receive All Pending
                            </button>
                        </div>
                        <div class="card-body p-4">
                            {{-- SCANNER INPUT --}}
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white border-primary">
                                            <i class="mdi mdi-barcode-scan"></i>
                                        </span>
                                        <input type="text" id="scannerInput" class="form-control border-primary"
                                            placeholder="Scan Barcode / UPC (Supports Long UPC codes)...">
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="mdi mdi-information-outline me-1"></i>
                                        Scan item to automatically find and focus it in the list.</small>
                                </div>
                            </div>

                            <form action="{{ route('warehouse.purchase-orders.receive', $purchaseOrder->id) }}"
                                method="POST">
                                @csrf

                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Vendor Invoice Number <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="mdi mdi-receipt"></i></span>
                                            <input type="text" name="invoice_number" class="form-control" required
                                                placeholder="e.g. INV-9988">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Duties ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i
                                                    class="mdi mdi-currency-usd"></i></span>
                                            <input type="number" name="duties" class="form-control" step="0.01"
                                                min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Shipping Cost ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i
                                                    class="mdi mdi-truck-delivery"></i></span>
                                            <input type="number" name="shipping_cost" class="form-control"
                                                step="0.01" min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Taxes ($)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="mdi mdi-percent"></i></span>
                                            <input type="number" name="taxes" class="form-control" step="0.01"
                                                min="0" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead class="bg-light text-uppercase small text-muted">
                                            <tr>
                                                <th class="px-3">UPC</th>
                                                <th class="px-3">Product</th>
                                                <th class="text-center">Ordered Qty</th>
                                                <th class="text-center">PO Price ($)</th>
                                                <th style="min-width: 130px;">Receive Qty</th>
                                                <th style="min-width: 130px;">Receiving Price ($)</th>
                                                <th style="min-width: 150px;">Batch No.</th>
                                                <th style="min-width: 140px;">Mfg Date</th>
                                                <th style="min-width: 140px;">Expiry Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $hasPendingItems = false; @endphp
                                            @foreach ($purchaseOrder->items as $item)
                                                @if ($item->pending_quantity > 0)
                                                    @php $hasPendingItems = true; @endphp
                                                    <tr>
                                                        <td class="px-3">
                                                            <span
                                                                class="badge bg-secondary">{{ $item->product->upc ?? 'N/A' }}</span>
                                                        </td>
                                                        <td class="px-3">
                                                            <span
                                                                class="fw-semibold text-dark">{{ $item->product->product_name }}</span><br>
                                                            <small class="text-muted">
                                                                UPC: {{ $item->product->upc }}
                                                                @if ($item->product->plu_code)
                                                                    | PLU: {{ $item->product->plu_code }}
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td class="text-center fw-medium">
                                                            {{ $item->requested_quantity }}
                                                        </td>
                                                        <td class="text-center fw-medium">$
                                                            {{ number_format($item->unit_cost, 2) }}</td>
                                                        <td class="text-center">
                                                            <input type="number"
                                                                name="items[{{ $item->id }}][receive_qty]"
                                                                class="form-control form-control-sm text-center fw-bold text-primary receive-qty-input"
                                                                max="{{ $item->pending_quantity }}" min="0"
                                                                value="0">
                                                            <small class="text-muted">Pending:
                                                                <span class="pending-qty" data-pending="{{ $item->pending_quantity }}">{{ $item->pending_quantity }}</span></small>
                                                        </td>
                                                        <td>
                                                            <input type="number"
                                                                name="items[{{ $item->id }}][receiving_price]"
                                                                class="form-control form-control-sm text-center fw-bold text-primary"
                                                                step="0.01" min="0"
                                                                value="{{ $item->unit_cost }}">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                name="items[{{ $item->id }}][batch_number]"
                                                                class="form-control form-control-sm"
                                                                placeholder="Auto if empty">
                                                        </td>
                                                        <td>
                                                            <input type="date"
                                                                name="items[{{ $item->id }}][mfg_date]"
                                                                class="form-control form-control-sm">
                                                        </td>
                                                        <td>
                                                            <input type="date"
                                                                name="items[{{ $item->id }}][expiry_date]"
                                                                class="form-control form-control-sm"
                                                                min="{{ date('Y-m-d') }}">
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach

                                            @if (!$hasPendingItems)
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-success fw-bold">
                                                        <i class="mdi mdi-check-circle-outline fs-3 d-block mb-2"></i>
                                                        All items for this order have been fully received.
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                @if ($hasPendingItems)
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn btn-primary shadow-sm px-4">
                                            <i class="mdi mdi-check-all me-1"></i> Process Receive
                                        </button>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                        <i class="mdi mdi-lock-alert me-3 fs-4"></i>
                        <div>
                            <strong>Access Restricted:</strong> Only Inventory Managers can receive stock.
                        </div>
                    </div>
                @endif

                {{-- RECEIVED ITEMS LOG --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="mdi mdi-history me-2 text-primary"></i> Receiving
                            History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-uppercase small text-muted">
                                    <tr>
                                        <th class="px-4 py-3">Product</th>
                                        <th class="py-3 text-center">Received Qty</th>
                                        <th class="py-3">Batch No</th>
                                        <th class="py-3 text-end px-4">Cost Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasReceived = false; @endphp
                                    @foreach ($purchaseOrder->items as $item)
                                        @if ($item->received_quantity > 0)
                                            @php $hasReceived = true; @endphp
                                            <tr>
                                                <td class="px-4 text-dark">
                                                    <div class="d-flex align-items-center">
                                                        @if ($item->product->main_image)
                                                            <img src="{{ Storage::disk('r2')->url($item->product->main_image) }}"
                                                                alt="" class="rounded"
                                                                style="width: 40px; height: 40px; object-fit: cover; margin-right: 12px;">
                                                        @endif
                                                        <div>
                                                            <div class="fw-semibold">{{ $item->product->product_name }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-success text-center">
                                                    +{{ $item->received_quantity }}</td>
                                                <td><span
                                                        class="badge bg-light text-dark border">{{ $item->batch_number ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-end px-4 fw-medium">
                                                    ${{ number_format($item->received_quantity * $item->unit_cost, 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    @if (!$hasReceived)
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No items have been received yet for this order.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scanner Logic
            const scannerInput = document.getElementById('scannerInput');
            if (scannerInput) {
                scannerInput.focus();
                scannerInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const barcode = this.value.trim();
                        if (!barcode) return;

                        let found = false;
                        document.querySelectorAll('.receive-qty-input').closest('tr').forEach(row => {
                            const upcText = row.querySelector('small.text-muted')?.innerText || '';
                            if (upcText.includes(barcode)) {
                                found = true;
                                // Highlight row
                                row.classList.add('table-primary');
                                setTimeout(() => row.classList.remove('table-primary'), 2000);

                                // Scroll & Focus
                                row.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                                const qtyInput = row.querySelector('.receive-qty-input');
                                if (qtyInput) {
                                    qtyInput.focus();
                                    qtyInput.select();
                                }
                            }
                        });

                        if (found) {
                            this.value = '';
                        }
                    }
                });
            }

            const receiveAllBtn = document.getElementById('receiveAllBtn');
            if (receiveAllBtn) {
                receiveAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('tr').forEach(row => {
                        const pendingEl = row.querySelector('.pending-qty');
                        const receiveInput = row.querySelector('.receive-qty-input');
                        if (pendingEl && receiveInput) {
                            const pendingVal = pendingEl.getAttribute('data-pending');
                            receiveInput.value = pendingVal;
                        }
                    });
                });
            }
        });
    </script>
@endpush
