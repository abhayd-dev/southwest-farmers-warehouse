<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stock-requests.index') }}">Requests</a></li>
                        <li class="breadcrumb-item active">#REQ-{{ $stockRequest->id }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">Process Request</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Store</label>
                            <h6 class="fw-bold">{{ $stockRequest->store->store_name }}</h6>
                            <small>{{ $stockRequest->store->city }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Product</label>
                            <div class="d-flex align-items-center mt-1">
                                @if($stockRequest->product->image)
                                    <img src="{{ asset('storage/'.$stockRequest->product->image) }}" class="rounded me-2" width="40">
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $stockRequest->product->name }}</h6>
                                    <small class="text-muted">SKU: {{ $stockRequest->product->sku }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mb-3">
                            <div class="col-4 border-end">
                                <small class="text-muted">Requested</small>
                                <h4 class="text-primary fw-bold">{{ $stockRequest->requested_quantity }}</h4>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted">Already Sent</small>
                                <h4 class="text-success fw-bold">{{ $stockRequest->fulfilled_quantity }}</h4>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Pending</small>
                                <h4 class="text-danger fw-bold">{{ $stockRequest->pending_quantity }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">Available in Warehouse</h6>
                        @php $totalStock = $stockRequest->product->batches->sum('quantity'); @endphp
                        
                        <h2 class="fw-bold {{ $totalStock < $stockRequest->pending_quantity ? 'text-danger' : 'text-success' }}">
                            {{ $totalStock }} <small class="fs-6 text-muted">units</small>
                        </h2>
                        
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">FIFO Batches (Auto-Selected):</small>
                            <ul class="list-group list-group-flush small">
                                @forelse($stockRequest->product->batches as $batch)
                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-1">
                                        <span>Exp: {{ $batch->expiry_date ?? 'N/A' }}</span>
                                        <span class="fw-bold">{{ $batch->quantity }} units</span>
                                    </li>
                                @empty
                                    <li class="list-group-item bg-transparent text-danger">No active batches found!</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Action: Dispatch or Reject</h5>
                    </div>
                    <div class="card-body">
                        @if($stockRequest->pending_quantity > 0 && $stockRequest->status != 'rejected')
                            <form action="{{ route('warehouse.stock-requests.update', $stockRequest->id) }}" method="POST">
                                @csrf
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Dispatch Quantity</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" name="dispatch_quantity" 
                                               class="form-control" 
                                               value="{{ min($stockRequest->pending_quantity, $totalStock) }}" 
                                               max="{{ min($stockRequest->pending_quantity, $totalStock) }}" 
                                               min="1">
                                        <span class="input-group-text">units</span>
                                    </div>
                                    <small class="text-muted">
                                        Max allowed: {{ min($stockRequest->pending_quantity, $totalStock) }}. 
                                        System will automatically deduct from oldest batches.
                                    </small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Note for Store Manager (Optional)</label>
                                    <textarea name="admin_note" class="form-control" rows="2" placeholder="e.g. Sent 30 now, rest will come next week..."></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-lg px-4 flex-grow-1">
                                        <i class="mdi mdi-truck-fast me-2"></i> Approve & Dispatch
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-danger btn-lg px-4" onclick="document.getElementById('rejectField').classList.remove('d-none')">
                                        Reject
                                    </button>
                                </div>

                                <div id="rejectField" class="mt-4 p-3 bg-danger bg-opacity-10 rounded d-none">
                                    <label class="form-label text-danger fw-bold">Reason for Rejection</label>
                                    <textarea name="admin_note_reject" class="form-control mb-2" rows="2" placeholder="Why are you rejecting this?"></textarea>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('rejectField').classList.add('d-none')">Cancel</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Confirm Reject</button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-secondary text-center py-4">
                                <i class="mdi mdi-check-circle-outline fs-1 d-block mb-2"></i>
                                @if($stockRequest->status == 'rejected')
                                    <h5 class="alert-heading">Request Rejected</h5>
                                    <p>{{ $stockRequest->admin_note }}</p>
                                @else
                                    <h5 class="alert-heading">Request Fully Fulfilled</h5>
                                    <p>All requested items have been dispatched.</p>
                                @endif
                                <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-sm btn-dark mt-2">Back to List</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>