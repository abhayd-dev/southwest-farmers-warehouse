<x-app-layout title="Stock Requests">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    
                    {{-- LEFT: TITLE --}}
                    <div class="d-flex flex-column gap-2">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-truck-delivery text-primary"></i>
                            Stock Requests Management
                        </h4>
                        <p class="text-muted mb-0 small">Manage store requests</p>
                    </div>

                    {{-- RIGHT: SEARCH & RESET --}}
                    <div class="flex-shrink-0">
                        <form method="GET" action="{{ route('warehouse.stock-requests.index') }}" class="d-flex gap-2 align-items-center">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by ID, Store, Product, SKU..." 
                                       value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                            </div>
                            @if(request('search'))
                                <a href="{{ route('warehouse.stock-requests.index', ['status' => request('status') ?? 'pending']) }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </a>
                            @endif
                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- TABS --}}
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

        {{-- STATS CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-clock-outline text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Pending Requests</p>
                                <h4 class="mb-0 fw-bold text-warning">{{ $pendingCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-truck text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">In Transit</p>
                                <h4 class="mb-0 fw-bold text-info">{{ $inTransitCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Completed</p>
                                <h4 class="mb-0 fw-bold text-success">{{ $completedCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-close-circle text-danger fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Rejected</p>
                                <h4 class="mb-0 fw-bold text-danger">{{ $rejectedCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="mdi mdi-format-list-bulleted text-primary"></i>
                    Requests List
                </h5>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-semibold small">#</th>
                                <th class="px-4 py-3 text-muted fw-semibold small">Request ID</th>
                                <th class="py-3 text-muted fw-semibold small">Store</th>
                                <th class="py-3 text-muted fw-semibold small">Product</th>
                                <th class="py-3 text-muted fw-semibold small text-center">Req. Qty</th>
                                <th class="py-3 text-muted fw-semibold small text-center">Status</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($requests as $req)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-muted">
                                        {{ $loop->iteration + ($requests->currentPage()-1)*$requests->perPage() }}
                                    </td>
                                    <td class="px-4 py-3 fw-bold">#{{ $req->id }}</td>
                                    <td class="py-3">
                                        <div class="fw-semibold text-dark">{{ $req->store->store_name }}</div>
                                        <small class="text-muted">{{ $req->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-semibold text-dark">{{ $req->product->product_name }}</div>
                                        <small class="text-muted">{{ $req->product->sku }}</small>
                                    </td>
                                    <td class="py-3 text-center">{{ $req->requested_quantity }}</td>
                                    <td class="py-3 text-center">
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
                                    <td class="px-4 py-3 text-end">
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
                                            @php $proof = $req->store_payment_proof ? Storage::url($req->store_payment_proof) : ''; @endphp
                                            <button class="btn btn-sm btn-outline-success ms-1"
                                                onclick="openVerifyModal({{ $req->id }}, '{{ $proof }}')">
                                                Verify Payment
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="mdi mdi-truck-delivery text-muted" style="font-size: 4rem;"></i>
                                            <p class="text-muted mt-3 mb-0">No requests found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($requests->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} entries
                        </div>
                        <div>
                            {{ $requests->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    {{-- Modals (Dispatch & Verify) remain the same as before --}}
    <div class="modal fade" id="dispatchModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="dispatchForm" class="modal-content" method="POST">
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
                        <label class="form-label">Remarks <span id="admin_note_star" class="text-danger d-none">*</span></label>
                        <textarea name="admin_note" id="admin_note" class="form-control" rows="2"></textarea>
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
            <form id="verifyForm" class="modal-content" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Verify Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="verify_req_id">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">STORE PAYMENT PROOF</label>
                        <div class="p-3 border rounded bg-light text-center" id="storeProofContainer"></div>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let maxQty = 0;

        function openDispatchModal(id, store, qty) {
            document.getElementById('dispatch_req_id').value = id;
            document.getElementById('dispatch_store').value = store;
            document.getElementById('dispatch_qty').value = qty;
            maxQty = parseInt(qty);

            document.getElementById('dispatch_status').value = 'dispatched';
            document.getElementById('dispatch_qty_div').style.display = 'block';
            document.getElementById('dispatch_qty').setAttribute('required', 'true');
            document.getElementById('admin_note_star').classList.add('d-none');
            document.getElementById('admin_note').removeAttribute('required');

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
                if (text.trim()) {
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Server returned invalid response');
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
                        html: data.message || 'Operation completed',
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
                    html: error.message || 'An unexpected error occurred'
                });
            });
        }

        const dispatchForm = document.getElementById('dispatchForm');
        if (dispatchForm) {
            dispatchForm.addEventListener('submit', function(e) {
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
        }

        const verifyFormIndex = document.getElementById('verifyForm');
        if (verifyFormIndex) {
            verifyFormIndex.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAjaxForm("{{ route('warehouse.stock-requests.verify-payment') }}", this);
            });
        }

        const dispatchStatus = document.getElementById('dispatch_status');
        if (dispatchStatus) {
            dispatchStatus.addEventListener('change', function() {
                const qtyDiv = document.getElementById('dispatch_qty_div');
                const qtyInput = document.getElementById('dispatch_qty');
                const star = document.getElementById('admin_note_star');
                const note = document.getElementById('admin_note');

                if (this.value === 'rejected') {
                    qtyDiv.style.display = 'none';
                    qtyInput.removeAttribute('required');
                    star.classList.remove('d-none');
                    note.setAttribute('required', 'true');
                } else {
                    qtyDiv.style.display = 'block';
                    qtyInput.setAttribute('required', 'true');
                    star.classList.add('d-none');
                    note.removeAttribute('required');
                }
            });
        }
    </script>
    @endpush
</x-app-layout>