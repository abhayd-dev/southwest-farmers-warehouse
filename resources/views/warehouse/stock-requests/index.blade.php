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
                                            $badge = match ($req->status) {
                                                'pending' => 'bg-warning text-dark',
                                                'dispatched' => 'bg-info',
                                                'completed' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary',
                                            };
                                            $statusLabel = str_replace('_', ' ', ucfirst($req->status));
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('warehouse.stock-requests.show', $req->id) }}"
                                            class="btn btn-sm btn-outline-dark shadow-sm">
                                            Manage
                                        </a>
                                        @if ($req->status == 'pending')
                                            <button class="btn btn-sm btn-outline-primary ms-1"
                                                onclick="openDispatchModal({{ $req->id }}, '{{ $req->store->store_name }}', '{{ $req->requested_quantity }}')">
                                                Change Status
                                            </button>
                                        @elseif($req->status == 'dispatched')
                                            @php $proof = $req->store_payment_proof ? asset('storage/'.$req->store_payment_proof) : ''; @endphp
                                            <button class="btn btn-sm btn-outline-success ms-1"
                                                onclick="openVerifyModal({{ $req->id }}, '{{ $proof }}')">
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
                        <input type="number" name="dispatch_quantity" id="dispatch_qty" class="form-control"
                            min="1">
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
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">STORE PAYMENT PROOF</label>
                        <div class="p-3 border rounded bg-light text-center" id="storeProofContainer">
                            <span class="text-muted small">Loading...</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Warehouse Payment Proof <span class="text-danger">*</span></label>
                        <input type="file" name="warehouse_payment_proof" class="form-control" required
                            accept="image/*,.pdf">
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
                            @foreach ($products as $prod)
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        function openVerifyModal(id, proofUrl) {
            document.getElementById('verify_req_id').value = id;
            const container = document.getElementById('storeProofContainer');

            if (proofUrl && proofUrl !== '') {
                container.innerHTML = `<a href="${proofUrl}" target="_blank" class="btn btn-sm btn-outline-info"><i class="mdi mdi-eye me-1"></i> View Store Proof</a>`;
            } else {
                container.innerHTML = `<span class="text-danger small"><i class="mdi mdi-alert-circle me-1"></i> No payment proof uploaded by store.</span>`;
            }

            new bootstrap.Modal(document.getElementById('verifyModal')).show();
        }

        function submitAjaxForm(url, formElement) {
            const formData = new FormData(formElement);

            fetch(url, {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.text().then(text => ({response, text})))
            .then(({response, text}) => {
                let data = {};
                if (text) {
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid server response (not JSON)');
                    }
                }

                if (!response.ok) {
                    let msg = data.message || 'Server error';
                    if (data.errors) {
                        msg = Object.values(data.errors).flat().join('<br>');
                    }
                    throw new Error(msg);
                }

                return data;
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        html: data.message || 'Operation completed successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        html: data.message || 'Operation failed'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: error.message
                });
            });
        }

        document.getElementById('dispatchForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const status = document.getElementById('dispatch_status').value;
            const qtyInput = document.getElementById('dispatch_qty');
            const qty = parseInt(qtyInput.value);

            if (status === 'dispatched') {
                if (qty <= 0 || qty > maxQty || isNaN(qty)) {
                    qtyInput.classList.add('is-invalid');
                    let errorSpan = document.querySelector('#dispatch_qty_div .invalid-feedback');
                    if (errorSpan) {
                        errorSpan.style.display = 'block';
                        errorSpan.innerText = "Quantity must be > 0 and <= " + maxQty;
                    }
                    return;
                }
                qtyInput.classList.remove('is-invalid');
            }

            submitAjaxForm("{{ route('warehouse.stock-requests.change-status') }}", this);
        });

        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitAjaxForm("{{ route('warehouse.stock-requests.verify-payment') }}", this);
        });

        document.getElementById('stockInForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitAjaxForm("{{ route('warehouse.stock-requests.purchase-in') }}", this);
        });

        document.getElementById('dispatch_status').addEventListener('change', function() {
            const qtyDiv = document.getElementById('dispatch_qty_div');
            const qtyInput = document.getElementById('dispatch_qty');
            if (this.value === 'rejected') {
                qtyDiv.style.display = 'none';
                qtyInput.removeAttribute('required');
            } else {
                qtyDiv.style.display = 'block';
                qtyInput.setAttribute('required', 'true');
            }
        });
    </script>
    @endpush
</x-app-layout>