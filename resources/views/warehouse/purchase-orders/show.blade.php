<x-app-layout title="PO Details">
    <div class="container-fluid">
        
        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="mdi mdi-home-outline"></i> Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('warehouse.purchase-orders.index') }}" class="text-decoration-none">Purchase Orders</a></li>
                                <li class="breadcrumb-item active" aria-current="page">#{{ $purchaseOrder->po_number }}</li>
                            </ol>
                        </nav>
                        <div class="d-flex align-items-center gap-3">
                            <h4 class="fw-bold mb-0 text-dark">
                                PO #{{ $purchaseOrder->po_number }}
                            </h4>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'ordered' => 'primary',
                                    'received' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $color = $statusColors[$purchaseOrder->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }} fs-6 px-3 py-2 rounded-pill text-uppercase">
                                {{ $purchaseOrder->status }}
                            </span>
                        </div>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="d-flex flex-wrap gap-2">
                        
                        {{-- PRINT LABELS --}}
                        @if ($purchaseOrder->items->sum('received_quantity') > 0)
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_po'))
                            <a href="{{ route('warehouse.purchase-orders.labels', $purchaseOrder->id) }}" class="btn btn-dark shadow-sm" target="_blank">
                                <i class="mdi mdi-barcode-scan me-1"></i> Print Labels
                            </a>
                            <a href="{{ route('warehouse.purchase-orders.receiving-history', $purchaseOrder->id) }}" class="btn btn-outline-info shadow-sm">
                                <i class="mdi mdi-history me-1"></i> Receiving History
                            </a>
                            @endif
                        @endif

                        {{-- PRINT PO --}}
                        <a href="{{ route('warehouse.purchase-orders.print', $purchaseOrder->id) }}" class="btn btn-outline-dark shadow-sm" target="_blank">
                            <i class="mdi mdi-printer me-1"></i> Print PO
                        </a>

                        {{-- SEND TO VENDOR --}}
                        @if ($purchaseOrder->status === 'draft' || $purchaseOrder->status === 'ordered')
                            <form action="{{ route('warehouse.purchase-orders.send-to-vendor', $purchaseOrder->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="send_email" value="1">
                                <button class="btn btn-success shadow-sm" type="submit">
                                    <i class="mdi mdi-email-send me-1"></i> Send to Vendor
                                </button>
                            </form>
                        @endif

                        {{-- MARK AS ORDERED --}}
                        @if ($purchaseOrder->status === 'draft')
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('approve_po'))
                            <form action="{{ route('warehouse.purchase-orders.mark-ordered', $purchaseOrder->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-info text-white shadow-sm">
                                    <i class="mdi mdi-send me-1"></i> Mark as Ordered
                                </button>
                            </form>
                            @endif
                        @endif

                        <a href="{{ route('warehouse.purchase-orders.index') }}" class="btn btn-outline-secondary shadow-sm">
                            <i class="mdi mdi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            {{-- VENDOR CARD --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="mdi mdi-domain fs-4"></i>
                            </div>
                            <h6 class="mb-0 text-uppercase text-muted fw-bold small">Vendor Details</h6>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $purchaseOrder->vendor->name }}</h5>
                        <div class="text-muted"><i class="mdi mdi-email-outline me-2"></i> {{ $purchaseOrder->vendor->email ?? 'N/A' }}</div>
                        <div class="text-muted"><i class="mdi mdi-phone-outline me-2"></i> {{ $purchaseOrder->vendor->phone ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- ORDER SUMMARY CARD --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-2 me-3">
                                <i class="mdi mdi-clipboard-text-outline fs-4"></i>
                            </div>
                            <h6 class="mb-0 text-uppercase text-muted fw-bold small">Order Summary</h6>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Order Date:</span>
                            <span class="fw-semibold">{{ $purchaseOrder->order_date->format('d M, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                            <span class="text-muted">Total Amount:</span>
                            <span class="fw-bold text-success fs-4">${{ number_format($purchaseOrder->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- APPROVAL STATUS SECTION --}}
        @if($purchaseOrder->approval_email)
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm border-start border-4 border-{{ $purchaseOrder->isApproved() ? 'success' : ($purchaseOrder->isRejected() ? 'danger' : 'warning') }}">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-{{ $purchaseOrder->isApproved() ? 'success' : ($purchaseOrder->isRejected() ? 'danger' : 'warning') }} bg-opacity-10 text-{{ $purchaseOrder->isApproved() ? 'success' : ($purchaseOrder->isRejected() ? 'danger' : 'warning') }} rounded p-3 me-3">
                                    <i class="mdi mdi-{{ $purchaseOrder->isApproved() ? 'check-circle' : ($purchaseOrder->isRejected() ? 'close-circle' : 'clock-outline') }} fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">Approval Status: 
                                        <span class="badge bg-{{ $purchaseOrder->isApproved() ? 'success' : ($purchaseOrder->isRejected() ? 'danger' : 'warning') }}">
                                            {{ strtoupper($purchaseOrder->approval_status) }}
                                        </span>
                                    </h6>
                                    <small class="text-muted">Approver: {{ $purchaseOrder->approval_email }}</small>
                                </div>
                            </div>
                            @if($purchaseOrder->approved_by_email)
                            <div class="text-end">
                                <small class="text-muted d-block">Actioned by: <strong>{{ $purchaseOrder->approved_by_email }}</strong></small>
                                @if($purchaseOrder->approved_at)
                                <small class="text-muted">{{ $purchaseOrder->approved_at->format('d M, Y H:i') }}</small>
                                @endif
                            </div>
                            @endif
                        </div>
                        @if($purchaseOrder->approval_reason)
                        <div class="mt-3 pt-3 border-top">
                            <strong class="text-muted small">Reason:</strong>
                            <p class="mb-0 mt-1">{{ $purchaseOrder->approval_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- RECEIVE SECTION (Protected & Conditional) --}}
        @if ($purchaseOrder->status !== 'draft' && $purchaseOrder->status !== 'completed' && $purchaseOrder->status !== 'cancelled')
            
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receive_po'))
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-primary mb-0"><i class="mdi mdi-truck-check me-2"></i> Receive Incoming Stock</h5>
                    <button type="button" id="receiveAllBtn" class="btn btn-sm btn-outline-primary">
                        <i class="mdi mdi-check-all me-1"></i> Receive All Pending
                    </button>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('warehouse.purchase-orders.receive', $purchaseOrder->id) }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Vendor Invoice Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="mdi mdi-receipt"></i></span>
                                    <input type="text" name="invoice_number" class="form-control" required placeholder="e.g. INV-9988">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="bg-light text-uppercase small text-muted">
                                    <tr>
                                        <th class="px-3">UPC</th>
                                        <th class="px-3">Product</th>
                                        <th class="text-center">Ordered</th>
                                        <th class="text-center">Pending</th>
                                        <th style="min-width: 130px;">Receive Now</th>
                                        <th style="min-width: 150px;">Batch No.</th>
                                        <th style="min-width: 140px;">Mfg Date</th>
                                        <th style="min-width: 140px;">Expiry Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchaseOrder->items as $item)
                                        @if ($item->pending_quantity > 0)
                                            <tr>
                                                <td class="px-3">
                                                    <span class="badge bg-secondary">{{ $item->product->barcode ?? 'N/A' }}</span>
                                                </td>
                                                <td class="px-3">
                                                    <span class="fw-semibold text-dark">{{ $item->product->product_name }}</span><br>
                                                    <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                                </td>
                                                <td class="text-center fw-medium">{{ $item->requested_quantity }}</td>
                                                <td class="text-center text-danger fw-bold pending-qty" data-pending="{{ $item->pending_quantity }}">{{ $item->pending_quantity }}</td>
                                                <td>
                                                    <input type="number" name="items[{{ $item->id }}][receive_qty]"
                                                           class="form-control form-control-sm text-center fw-bold text-primary receive-qty-input"
                                                           max="{{ $item->pending_quantity }}" min="0" value="0">
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $item->id }}][batch_number]" class="form-control form-control-sm" placeholder="Auto if empty">
                                                </td>
                                                <td>
                                                    <input type="date" name="items[{{ $item->id }}][mfg_date]" class="form-control form-control-sm">
                                                </td>
                                                <td>
                                                    <input type="date" name="items[{{ $item->id }}][expiry_date]" class="form-control form-control-sm" min="{{ date('Y-m-d') }}">
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary shadow-sm px-4">
                                <i class="mdi mdi-check-all me-1"></i> Process Receive
                            </button>
                        </div>
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
        @endif

        {{-- ITEMS LIST (READ ONLY) --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-file-document-outline me-1"></i> Order Items Details</h6>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="px-4 py-3">Product</th>
                                <th class="py-3">Unit Cost</th>
                                <th class="py-3 text-center">Qty Ordered</th>
                                <th class="py-3 text-center">Qty Received</th>
                                <th class="py-3 text-end">Total Cost</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $item->product->product_name }}</span>
                                            <small class="text-muted">{{ $item->product->sku }}</small>
                                        </div>
                                    </td>
                                    <td class="py-3">$ {{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="py-3 text-center">{{ $item->requested_quantity }}</td>
                                    <td class="py-3 text-center">
                                        <span class="fw-bold {{ $item->received_quantity == $item->requested_quantity ? 'text-success' : 'text-warning' }}">
                                            {{ $item->received_quantity }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-end fw-semibold">$ {{ number_format($item->total_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($item->received_quantity >= $item->requested_quantity)
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Full</span>
                                        @elseif($item->received_quantity > 0)
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">Partial</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push("scripts")
    <script>
        // Receive All Pending — fills all receive_qty inputs with their pending quantity
        document.getElementById("receiveAllBtn")?.addEventListener("click", function() {
            document.querySelectorAll(".receive-qty-input").forEach(function(input) {
                const row = input.closest("tr");
                const pendingCell = row.querySelector(".pending-qty");
                if (pendingCell) {
                    input.value = pendingCell.dataset.pending;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
