<x-app-layout title="Process Request #{{ $stockRequest->id }}">
    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stock-requests.index') }}">Requests</a></li>
                        <li class="breadcrumb-item active">#REQ-{{ $stockRequest->id }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">Dispatch Stock</h4>
            </div>
            <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            {{-- Left Column: Request Details --}}
            <div class="col-md-4">
                {{-- Store Info --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0 fw-bold text-muted">Request Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-bold">Store</label>
                            <h6 class="fw-bold text-dark mb-0">{{ $stockRequest->store->store_name }}</h6>
                            <small class="text-muted">{{ $stockRequest->store->city }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-bold">Product</label>
                            <div class="d-flex align-items-center mt-2">
                                <div class="bg-light rounded p-2 border me-3">
                                    <i class="mdi mdi-box-seam text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $stockRequest->product->product_name }}</h6>
                                    <small class="text-muted">SKU: {{ $stockRequest->product->sku }}</small>
                                </div>
                            </div>
                        </div>
                        <hr class="border-light">
                        
                        {{-- Quantities --}}
                        <div class="row text-center">
                            <div class="col-4 border-end">
                                <small class="text-muted d-block mb-1">Requested</small>
                                <h5 class="text-primary fw-bold mb-0">{{ $stockRequest->requested_quantity }}</h5>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted d-block mb-1">Sent</small>
                                <h5 class="text-success fw-bold mb-0">{{ $stockRequest->fulfilled_quantity }}</h5>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block mb-1">Pending</small>
                                <h5 class="text-danger fw-bold mb-0">{{ $stockRequest->pending_quantity }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Warehouse Stock Info --}}
                <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                    <div class="card-body">
                        <h6 class="card-title text-info fw-bold mb-3"><i class="mdi mdi-warehouse me-1"></i> Warehouse Availability</h6>
                        @php $totalStock = $stockRequest->product->batches->sum('quantity'); @endphp
                        
                        <div class="d-flex align-items-baseline mb-2">
                            <h2 class="fw-bold mb-0 {{ $totalStock < $stockRequest->pending_quantity ? 'text-danger' : 'text-dark' }}">
                                {{ $totalStock }}
                            </h2>
                            <small class="text-muted ms-2">{{ $stockRequest->product->unit ?? 'Units' }} available</small>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">FIFO Batches (Auto-Selected)</small>
                            <ul class="list-group list-group-flush bg-transparent mt-1">
                                @forelse($stockRequest->product->batches->take(3) as $batch)
                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-1 border-bottom border-info border-opacity-25">
                                        <span class="small text-dark">{{ $batch->batch_number }} <span class="text-muted">(Exp: {{ $batch->expiry_date ? $batch->expiry_date->format('M Y') : 'N/A' }})</span></span>
                                        <span class="small fw-bold">{{ $batch->quantity }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item bg-transparent text-danger small fst-italic">No active batches found!</li>
                                @endforelse
                                @if($stockRequest->product->batches->count() > 3)
                                    <li class="list-group-item bg-transparent text-center small text-muted py-1">+{{ $stockRequest->product->batches->count() - 3 }} more batches</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Action Form --}}
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold text-dark">Process Dispatch</h6>
                    </div>
                    <div class="card-body p-4">
                        @if($stockRequest->pending_quantity > 0 && $stockRequest->status != 'rejected')
                            <form action="{{ route('warehouse.stock-requests.update', $stockRequest->id) }}" method="POST">
                                @csrf
                                
                                {{-- Dispatch Input --}}
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Quantity to Dispatch <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" name="dispatch_quantity" 
                                               class="form-control fw-bold" 
                                               value="{{ min($stockRequest->pending_quantity, $totalStock) }}" 
                                               max="{{ min($stockRequest->pending_quantity, $totalStock) }}" 
                                               min="1">
                                        <span class="input-group-text bg-light text-muted">{{ $stockRequest->product->unit ?? 'Units' }}</span>
                                    </div>
                                    <div class="form-text text-muted mt-2">
                                        <i class="mdi mdi-information-outline"></i> 
                                        System will automatically deduct from the oldest batches first (FIFO).
                                        Max dispatchable: <strong>{{ min($stockRequest->pending_quantity, $totalStock) }}</strong>
                                    </div>
                                </div>

                                {{-- Note Input --}}
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Admin Note <span class="text-muted fw-normal">(Optional)</span></label>
                                    <textarea name="admin_note" class="form-control" rows="3" placeholder="Add a message for the store manager (e.g. 'Partial shipment sent, rest arriving Monday')..."></textarea>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="d-flex gap-3 pt-2">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-lg px-5 shadow-sm">
                                        <i class="mdi mdi-truck-fast me-2"></i> Approve & Dispatch
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-danger btn-lg px-4" onclick="document.getElementById('rejectSection').classList.remove('d-none')">
                                        <i class="mdi mdi-close-circle me-1"></i> Reject
                                    </button>
                                </div>

                                {{-- Hidden Reject Section --}}
                                <div id="rejectSection" class="mt-4 p-3 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25 d-none">
                                    <h6 class="text-danger fw-bold mb-2">Reject Request</h6>
                                    <label class="form-label small text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                                    <textarea name="admin_note_reject" class="form-control mb-3" rows="2" placeholder="Why are you rejecting this request?"></textarea>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('rejectSection').classList.add('d-none')">Cancel</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm px-3">Confirm Rejection</button>
                                    </div>
                                </div>
                            </form>
                        @else
                            {{-- Fulfilled/Rejected State --}}
                            <div class="text-center py-5">
                                @if($stockRequest->status == 'rejected')
                                    <div class="mb-3">
                                        <i class="mdi mdi-close-circle text-danger opacity-50" style="font-size: 4rem;"></i>
                                    </div>
                                    <h4 class="fw-bold text-danger">Request Rejected</h4>
                                    <p class="text-muted max-w-sm mx-auto">Reason: {{ $stockRequest->admin_note }}</p>
                                @else
                                    <div class="mb-3">
                                        <i class="mdi mdi-check-decagram text-success opacity-50" style="font-size: 4rem;"></i>
                                    </div>
                                    <h4 class="fw-bold text-success">Order Fulfilled</h4>
                                    <p class="text-muted">All requested items have been dispatched to the store.</p>
                                @endif
                                <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-dark mt-3">Return to Dashboard</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>