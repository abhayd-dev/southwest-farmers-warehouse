<x-app-layout title="Stock Requests">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="mdi mdi-truck-delivery me-2"></i> Stock Requests Management
                </h4>
                <p class="text-muted mb-0 small mt-1">Manage store requests and internal stock</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#stockInModal">
                    <i class="mdi mdi-plus-circle me-1"></i> Purchase In
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'pending' || !request('status') ? 'active fw-bold' : '' }}" 
                   href="{{ route('warehouse.stock-requests.index', ['status' => 'pending']) }}">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'in_transit' ? 'active fw-bold' : '' }}" 
                   href="{{ route('warehouse.stock-requests.index', ['status' => 'in_transit']) }}">In Transit</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request('status') == 'history' ? 'active fw-bold' : '' }}" 
                   href="{{ route('warehouse.stock-requests.index', ['status' => 'history']) }}">History</a>
            </li>
        </ul>

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4">Request ID</th>
                                <th>Store</th>
                                <th>Product</th>
                                <th>Req. Qty</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr>
                                <td class="ps-4 fw-bold">#{{ $req->id }}</td>
                                <td>
                                    <span class="d-block fw-bold">{{ $req->store->store_name }}</span>
                                    <small class="text-muted">{{ $req->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <span class="d-block fw-bold">{{ $req->product->product_name }}</span>
                                    <small class="text-muted">{{ $req->product->sku }}</small>
                                </td>
                                <td>{{ $req->requested_quantity }}</td>
                                <td>
                                    @php
                                        $badge = match($req->status) {
                                            'pending' => 'bg-warning text-dark',
                                            'dispatched' => 'bg-info',
                                            'completed' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $statusLabel = str_replace('_', ' ', ucfirst($req->status));
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('warehouse.stock-requests.show', $req->id) }}" class="btn btn-sm btn-outline-dark shadow-sm">
                                        Manage
                                    </a>
                                    @if($req->status == 'pending')
                                        <button class="btn btn-sm btn-outline-primary ms-1" onclick="openDispatchModal({{ $req->id }}, '{{ $req->store->store_name }}', '{{ $req->requested_quantity }}')">
                                            Change Status
                                        </button>
                                    @elseif($req->status == 'dispatched')
                                        <button class="btn btn-sm btn-outline-success ms-1" onclick="openVerifyModal({{ $req->id }})">
                                            Verify Payment
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No requests found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3">
                {{ $requests->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    {{-- Change Status / Dispatch Modal --}}
    <div class="modal fade" id="dispatchModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="dispatchForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Process Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="dispatch_req_id">
                    <div class="mb-3">
                        <label class="form-label">Store</label>
                        <input type="text" class="form-control" id="dispatch_store" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="dispatch_status" class="form-select">
                            <option value="dispatched">Dispatched</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3" id="dispatch_qty_div">
                        <label class="form-label">Quantity to Dispatch <span class="text-danger">*</span></label>
                        <input type="number" name="dispatch_quantity" id="dispatch_qty" class="form-control" min="1">
                        <div class="invalid-feedback">Must be greater than 0 and less than requested.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="admin_note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Verify Payment Modal --}}
    <div class="modal fade" id="verifyModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="verifyForm" class="modal-content" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Verify Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="verify_req_id">
                    
                    {{-- Store Proof Display --}}
                    <div class="mb-3">
                        <label class="form-label text-muted small">Store Payment Proof</label>
                        <div class="p-2 border rounded bg-light text-center">
                            {{-- In a real scenario, fetch this via AJAX or pass data. Placeholder for now --}}
                            <span class="text-muted small">View via Manage Page</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Warehouse Payment Proof <span class="text-danger">*</span></label>
                        <input type="file" name="warehouse_payment_proof" class="form-control" required accept="image/*,.pdf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea name="warehouse_remarks" class="form-control" required rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Complete Verification</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stock In Modal --}}
    <div class="modal fade" id="stockInModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="stockInForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Purchase In (Direct Stock)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->product_name }} ({{ $prod->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purchase Reference</label>
                        <input type="text" name="purchase_ref" class="form-control" placeholder="PO-XXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let maxQty = 0;

        function openDispatchModal(id, store, qty) {
            document.getElementById('dispatch_req_id').value = id;
            document.getElementById('dispatch_store').value = store;
            document.getElementById('dispatch_qty').value = qty;
            maxQty = parseInt(qty);
            
            const modal = new bootstrap.Modal(document.getElementById('dispatchModal'));
            modal.show();
        }

        function openVerifyModal(id) {
            document.getElementById('verify_req_id').value = id;
            new bootstrap.Modal(document.getElementById('verifyModal')).show();
        }

        // Dispatch Validation
        document.getElementById('dispatch_status').addEventListener('change', function() {
            const qtyDiv = document.getElementById('dispatch_qty_div');
            const qtyInput = document.getElementById('dispatch_qty');
            if(this.value === 'rejected') {
                qtyDiv.style.display = 'none';
                qtyInput.removeAttribute('required');
            } else {
                qtyDiv.style.display = 'block';
                qtyInput.setAttribute('required', 'true');
            }
        });

        document.getElementById('dispatchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const status = document.getElementById('dispatch_status').value;
            const qtyInput = document.getElementById('dispatch_qty');
            const qty = parseInt(qtyInput.value);

            if(status === 'dispatched') {
                if(qty <= 0 || qty > maxQty || isNaN(qty)) {
                    qtyInput.classList.add('is-invalid');
                    qtyInput.style.borderColor = 'red';
                    
                    // Show inline error if not present
                    let errorSpan = qtyInput.nextElementSibling;
                    if(!errorSpan || !errorSpan.classList.contains('invalid-feedback')) {
                        errorSpan = document.querySelector('#dispatch_qty_div .invalid-feedback');
                    }
                    errorSpan.style.display = 'block';
                    errorSpan.innerText = "Quantity must be > 0 and <= " + maxQty;
                    return;
                }
                qtyInput.classList.remove('is-invalid');
                qtyInput.style.borderColor = '';
                document.querySelector('#dispatch_qty_div .invalid-feedback').style.display = 'none';
            }

            const formData = new FormData(this);
            fetch("{{ route('warehouse.stock-requests.change-status') }}", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) location.reload();
                else alert(data.message);
            });
        });

        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch("{{ route('warehouse.stock-requests.verify-payment') }}", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) location.reload();
                else alert(data.message);
            });
        });

        document.getElementById('stockInForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch("{{ route('warehouse.stock-requests.purchase-in') }}", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) location.reload();
                else alert(data.message);
            });
        });
    </script>
    @endpush
</x-app-layout>