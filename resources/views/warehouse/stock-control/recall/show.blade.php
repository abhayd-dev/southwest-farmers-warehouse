<x-app-layout title="Recall Request #{{ $recall->id }}">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Recall Request #' . $recall->id])

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="mdi mdi-undo-variant text-warning me-2"></i> 
                Recall Request #{{ $recall->id }}
            </h4>
            <p class="text-muted mb-0">
                {{ $recall->store->store_name }} → Warehouse
            </p>
        </div>
        <div>
            <span class="badge bg-{{ $recall->getStatusColor() }} fs-6 px-3 py-2">
                {{ $recall->getStatusLabel() }}
            </span>
            <a href="{{ route('warehouse.stock-control.recall') }}" class="btn btn-outline-secondary ms-2">Back</a>
        </div>
    </div>

    <div class="row g-4">
        {{-- DETAILS CARD --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Details</h5></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Product:</strong> {{ $recall->product->product_name }}</div>
                        <div class="col-md-6"><strong>SKU:</strong> {{ $recall->product->sku }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Requested Qty:</strong> {{ $recall->requested_quantity }}</div>
                        <div class="col-md-6"><strong>Reason:</strong> {{ ucwords(str_replace('_', ' ', $recall->reason)) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Initiated By:</strong> {{ $recall->initiated_by == Auth::id() ? 'Warehouse (Me)' : 'Store' }}</div>
                        <div class="col-md-6"><strong>Date:</strong> {{ $recall->created_at->format('d M Y') }}</div>
                    </div>
                    @if($recall->reason_remarks)
                        <div class="alert alert-light border">{{ $recall->reason_remarks }}</div>
                    @endif
                </div>
            </div>

            {{-- ACTIONS CARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0 fw-bold text-primary">Actions</h5></div>
                <div class="card-body p-4">

                    {{-- 1. STORE REQUESTED -> WAREHOUSE APPROVAL --}}
                    @if($recall->status == 'pending_warehouse_approval')
                        <div class="alert alert-warning border-warning">
                            <i class="mdi mdi-alert-circle me-1"></i> <strong>Action Required:</strong> Store requested to return this stock.
                        </div>
                        <form action="{{ route('warehouse.stock-control.recall.approve', $recall->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="fw-bold">Approve Quantity</label>
                                <input type="number" name="approved_quantity" class="form-control" 
                                       value="{{ $recall->requested_quantity }}" max="{{ $recall->requested_quantity }}" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label>Remarks (Optional)</label>
                                <textarea name="warehouse_remarks" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success px-4">Approve Request</button>
                            <button type="button" class="btn btn-outline-danger px-4 ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                        </form>

                    {{-- 2. STORE DISPATCHED -> WAREHOUSE RECEIVE --}}
                    @elseif($recall->status == 'dispatched')
                        <div class="alert alert-info border-info">
                            <i class="mdi mdi-truck-fast me-1"></i> <strong>Incoming:</strong> Store has dispatched {{ $recall->dispatched_quantity }} units.
                        </div>
                        <form action="{{ route('warehouse.stock-control.recall.receive', $recall->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="fw-bold">Received Quantity</label>
                                <input type="number" name="received_quantity" class="form-control" 
                                       value="{{ $recall->dispatched_quantity }}" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label>Receipt Remarks</label>
                                <textarea name="warehouse_remarks" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary px-4">Confirm Receipt</button>
                        </form>

                    {{-- 3. WAITING STATES --}}
                    @elseif($recall->status == 'approved')
                        <div class="alert alert-primary">
                            <i class="mdi mdi-clock me-1"></i> Approved. Waiting for Store to Dispatch.
                        </div>
                    @elseif($recall->status == 'pending_store_approval')
                        <div class="alert alert-warning">
                            <i class="mdi mdi-clock me-1"></i> Waiting for Store to Accept your request.
                        </div>
                    @elseif($recall->status == 'completed')
                        <div class="alert alert-success">
                            <i class="mdi mdi-check-circle me-1"></i> Process Completed. Stock Added to Warehouse.
                        </div>
                    @else
                        <div class="alert alert-secondary">
                            Status: {{ $recall->getStatusLabel() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- REJECT MODAL --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('warehouse.stock-control.recall.reject', $recall->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title text-danger">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Reason <span class="text-danger">*</span></label>
                <textarea name="warehouse_remarks" class="form-control" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

</x-app-layout>