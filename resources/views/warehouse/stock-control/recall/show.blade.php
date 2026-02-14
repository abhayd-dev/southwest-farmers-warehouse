<x-app-layout title="Recall Request #{{ $recall->id }}">

<div class="container-fluid">

    {{-- HEADER SECTION --}}
    <div class="bg-white border-bottom shadow-sm mb-4">
        <div class="py-3">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div class="d-flex flex-column gap-1">
                    @include('warehouse.partials.breadcrumb', ['title' => 'Recall Request #' . $recall->id])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-undo-variant text-warning me-2"></i> 
                        Recall Request #{{ $recall->id }}
                    </h4>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="mdi mdi-store me-1"></i> {{ $recall->store->store_name }} 
                        <i class="mdi mdi-arrow-right mx-2"></i> 
                        <i class="mdi mdi-warehouse me-1"></i> Warehouse
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $recall->getStatusColor() }} fs-6 px-3 py-2 rounded-pill shadow-sm">
                        {{ $recall->getStatusLabel() }}
                    </span>
                    <a href="{{ route('warehouse.stock-control.recall') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="mdi mdi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- LEFT COLUMN: DETAILS CARD --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-file-document-outline me-1"></i> Request Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="small text-muted text-uppercase fw-bold">Product Information</label>
                        <div class="d-flex align-items-center mt-2">
                            <div class="bg-light rounded p-2 me-3 border">
                                <i class="mdi mdi-package-variant-closed text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">{{ $recall->product->product_name }}</h6>
                                <small class="text-muted font-monospace">SKU: {{ $recall->product->sku }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="small text-muted text-uppercase fw-bold">Requested Qty</label>
                            <h5 class="fw-bold text-dark mt-1">{{ $recall->requested_quantity }}</h5>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted text-uppercase fw-bold">Created Date</label>
                            <h6 class="fw-bold text-dark mt-1">{{ $recall->created_at->format('d M Y') }}</h6>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Reason</label>
                        <div class="mt-1">
                            <span class="badge bg-light text-dark border px-2 py-1">
                                {{ ucwords(str_replace('_', ' ', $recall->reason)) }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted text-uppercase fw-bold">Initiated By</label>
                        <div class="d-flex align-items-center mt-1 text-dark">
                            <i class="mdi mdi-account-circle-outline me-1 text-secondary"></i>
                            {{ $recall->initiated_by == Auth::id() ? 'Warehouse (Me)' : 'Store' }}
                        </div>
                    </div>

                    @if($recall->reason_remarks)
                        <div class="alert alert-light border border-secondary border-opacity-25 mt-3 mb-0">
                            <small class="fw-bold text-muted d-block mb-1">Remarks:</small>
                            <i class="mdi mdi-format-quote-open text-muted me-1"></i>
                            {{ $recall->reason_remarks }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: ACTIONS & WORKFLOW --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="mdi mdi-list-status me-1"></i> Workflow & Actions</h6>
                </div>
                <div class="card-body p-4">

                    {{-- 1. STORE REQUESTED -> WAREHOUSE APPROVAL --}}
                    @if($recall->status == 'pending_warehouse_approval')
                        <div class="alert alert-warning border-0 border-start border-4 border-warning shadow-sm d-flex align-items-start mb-4">
                            <i class="mdi mdi-alert-circle fs-4 me-3 mt-1"></i> 
                            <div>
                                <h6 class="fw-bold mb-1">Action Required</h6>
                                <p class="mb-0 small">The store has requested to return this stock. Please review and approve or reject.</p>
                            </div>
                        </div>
                        
                        <div class="bg-light p-4 rounded border">
                            <form action="{{ route('warehouse.stock-control.recall.approve', $recall->id) }}" method="POST">
                                @csrf
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Approve Quantity <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="approved_quantity" class="form-control" 
                                                value="{{ $recall->requested_quantity }}" max="{{ $recall->requested_quantity }}" min="1" required>
                                            <span class="input-group-text bg-white text-muted">Units</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Remarks <span class="text-muted fw-normal">(Optional)</span></label>
                                        <textarea name="warehouse_remarks" class="form-control" rows="2" placeholder="Add approval notes..."></textarea>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-success shadow-sm px-4">
                                        <i class="mdi mdi-check-circle me-1"></i> Approve Request
                                    </button>
                                    <button type="button" class="btn btn-outline-danger shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                        <i class="mdi mdi-close-circle me-1"></i> Reject
                                    </button>
                                </div>
                            </form>
                        </div>

                    {{-- 2. STORE DISPATCHED -> WAREHOUSE RECEIVE --}}
                    @elseif($recall->status == 'dispatched')
                        <div class="alert alert-info border-0 border-start border-4 border-info shadow-sm d-flex align-items-start mb-4">
                            <i class="mdi mdi-truck-fast fs-4 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Stock Incoming</h6>
                                <p class="mb-0 small">Store has dispatched <strong>{{ $recall->dispatched_quantity }}</strong> units. Confirm receipt once verified.</p>
                            </div>
                        </div>

                        <div class="bg-light p-4 rounded border">
                            <form action="{{ route('warehouse.stock-control.recall.receive', $recall->id) }}" method="POST">
                                @csrf
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Received Quantity <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="received_quantity" class="form-control" 
                                                value="{{ $recall->dispatched_quantity }}" min="1" required>
                                            <span class="input-group-text bg-white text-muted">Units</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Receipt Remarks <span class="text-muted fw-normal">(Optional)</span></label>
                                        <textarea name="warehouse_remarks" class="form-control" rows="2" placeholder="Condition of goods, etc."></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary shadow-sm px-4">
                                    <i class="mdi mdi-package-variant-closed me-1"></i> Confirm Receipt
                                </button>
                            </form>
                        </div>

                    {{-- 3. WAITING STATES --}}
                    @elseif($recall->status == 'approved')
                        <div class="text-center py-5">
                            <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3">
                                <i class="mdi mdi-clock-outline text-primary fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Approved & Waiting</h5>
                            <p class="text-muted">You have approved this request. Waiting for the Store to dispatch the stock.</p>
                        </div>

                    @elseif($recall->status == 'pending_store_approval')
                        <div class="text-center py-5">
                            <div class="avatar-lg bg-warning bg-opacity-10 rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3">
                                <i class="mdi mdi-account-clock text-warning fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Pending Store Action</h5>
                            <p class="text-muted">Request sent. Waiting for the Store Manager to review and accept.</p>
                        </div>

                    @elseif($recall->status == 'completed')
                        <div class="text-center py-5">
                            <div class="avatar-lg bg-success bg-opacity-10 rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3">
                                <i class="mdi mdi-check-decagram text-success fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-success">Recall Completed</h5>
                            <p class="text-muted">Stock has been successfully returned and added back to warehouse inventory.</p>
                        </div>

                    @else
                        <div class="alert alert-secondary d-flex align-items-center">
                            <i class="mdi mdi-information me-2"></i> Current Status: <strong>{{ $recall->getStatusLabel() }}</strong>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- REJECT MODAL --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('warehouse.stock-control.recall.reject', $recall->id) }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="mdi mdi-alert-circle-outline me-1"></i> Reject Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                    <textarea name="warehouse_remarks" class="form-control" rows="4" placeholder="Please provide a reason..." required></textarea>
                </div>
                <div class="alert alert-light border small text-muted">
                    <i class="mdi mdi-information me-1"></i> The store manager will be notified of this rejection.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger px-4">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

</x-app-layout>