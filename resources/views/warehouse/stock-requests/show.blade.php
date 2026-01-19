<x-app-layout title="Process Request #{{ $stockRequest->id }}">
    <div class="container-fluid">
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

            <div class="d-flex gap-2">
                <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-success">
                    <i class="mdi mdi-plus-box me-1"></i> Stock In (Purchase)
                </a>

                <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
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

                        <div class="row text-center">
                            <div class="col-4 border-end">
                                <small class="text-muted d-block mb-1">Requested</small>
                                <h5 class="text-primary fw-bold mb-0" id="requested-qty">
                                    {{ $stockRequest->requested_quantity }}</h5>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted d-block mb-1">Sent</small>
                                <h5 class="text-success fw-bold mb-0">{{ $stockRequest->fulfilled_quantity }}</h5>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block mb-1">Pending</small>
                                <h5 class="text-danger fw-bold mb-0" id="pending-qty">
                                    {{ $stockRequest->pending_quantity }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

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
                                @if ($stockRequest->product->batches->count() > 3)
                                    <li class="list-group-item bg-transparent text-center small text-muted py-1">+{{ $stockRequest->product->batches->count() - 3 }} more batches</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold text-dark">Process Dispatch</h6>
                    </div>
                    <div class="card-body p-4">
                        @if ($stockRequest->pending_quantity > 0 && $stockRequest->status != 'rejected')
                            <form id="dispatchForm" class="needs-validation" novalidate>
                                @csrf
                                <input type="hidden" name="request_id" value="{{ $stockRequest->id }}">
                                <input type="hidden" name="status" value="dispatched">

                                <div class="mb-4" id="dispatch_qty_div">
                                    <label class="form-label fw-bold">Quantity to Dispatch <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg has-validation">
                                        <input type="number" name="dispatch_quantity" id="dispatch_qty"
                                            class="form-control fw-bold"
                                            value="{{ min($stockRequest->pending_quantity, $totalStock) }}"
                                            max="{{ min($stockRequest->pending_quantity, $totalStock) }}"
                                            min="1" required>
                                        <span class="input-group-text bg-light text-muted">{{ $stockRequest->product->unit ?? 'Units' }}</span>
                                        <div class="invalid-feedback fw-bold" id="qty-error">
                                            Please enter a valid quantity (1 - {{ min($stockRequest->pending_quantity, $totalStock) }}).
                                        </div>
                                    </div>
                                    <div class="form-text text-muted mt-2">
                                        <i class="mdi mdi-information-outline"></i>
                                        System will automatically deduct from the oldest batches first (FIFO).
                                        Max dispatchable: <strong>{{ min($stockRequest->pending_quantity, $totalStock) }}</strong>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Admin Note <span class="text-muted fw-normal">(Optional)</span></label>
                                    <textarea name="admin_note" class="form-control" rows="3" placeholder="Add a message for the store manager..."></textarea>
                                </div>

                                <div class="d-flex gap-3 pt-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                        <i class="mdi mdi-truck-fast me-2"></i> Approve & Dispatch
                                    </button>

                                    <button type="button" class="btn btn-outline-danger btn-lg px-4"
                                        onclick="toggleRejectSection()">
                                        <i class="mdi mdi-close-circle me-1"></i> Reject
                                    </button>
                                </div>
                            </form>

                            <div id="rejectSection"
                                class="mt-4 p-3 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25 d-none">
                                <form id="rejectForm">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $stockRequest->id }}">
                                    <input type="hidden" name="status" value="rejected">

                                    <h6 class="text-danger fw-bold mb-2">Reject Request</h6>
                                    <label class="form-label small text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                                    <textarea name="admin_note" class="form-control mb-3" rows="2"
                                        placeholder="Why are you rejecting this request?" required></textarea>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light btn-sm"
                                            onclick="toggleRejectSection()">Cancel</button>
                                        <button type="submit" class="btn btn-danger btn-sm px-3">Confirm Rejection</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="text-center py-5">
                                @if ($stockRequest->status == 'rejected')
                                    <div class="mb-3">
                                        <i class="mdi mdi-close-circle text-danger opacity-50" style="font-size: 4rem;"></i>
                                    </div>
                                    <h4 class="fw-bold text-danger">Request Rejected</h4>
                                    <p class="text-muted max-w-sm mx-auto">Reason: {{ $stockRequest->admin_note }}</p>
                                @elseif($stockRequest->status == 'dispatched')
                                    <div class="mb-3">
                                        <i class="mdi mdi-truck-check text-info opacity-50" style="font-size: 4rem;"></i>
                                    </div>
                                    <h4 class="fw-bold text-info">Dispatched & Pending Payment</h4>
                                    <p class="text-muted">Items dispatched. Waiting for payment verification.</p>
                                    <button class="btn btn-primary mt-3"
                                        onclick="openVerifyModal({{ $stockRequest->id }})">
                                        Verify Payment Now
                                    </button>
                                @else
                                    <div class="mb-3">
                                        <i class="mdi mdi-check-decagram text-success opacity-50" style="font-size: 4rem;"></i>
                                    </div>
                                    <h4 class="fw-bold text-success">Order Completed</h4>
                                    <p class="text-muted">Stock received and payment verified.</p>

                                    <div class="mt-4 p-3 bg-light rounded text-start mx-auto" style="max-width: 400px;">
                                        <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-2">Verification Details</h6>

                                        <div class="mb-2">
                                            <small class="d-block text-muted fw-bold">Store Proof:</small>
                                            @if ($stockRequest->store_payment_proof)
                                                <a href="{{ asset('storage/'.$stockRequest->store_payment_proof) }}" target="_blank" class="text-primary text-decoration-none">
                                                    <i class="mdi mdi-file-document me-1"></i> View Store File
                                                </a>
                                            @else
                                                <span class="text-danger small">No proof uploaded by store.</span>
                                            @endif
                                        </div>

                                        <div>
                                            <small class="d-block text-muted fw-bold">Warehouse Proof:</small>
                                            @if ($stockRequest->warehouse_payment_proof)
                                                <a href="{{ asset('storage/'.$stockRequest->warehouse_payment_proof) }}" target="_blank" class="text-primary text-decoration-none">
                                                    <i class="mdi mdi-file-check me-1"></i> View Warehouse File
                                                </a>
                                            @else
                                                <span class="text-muted small">Pending upload.</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <a href="{{ route('warehouse.stock-requests.index') }}"
                                    class="btn btn-dark mt-3 d-block mx-auto" style="width: fit-content;">Return to Dashboard</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
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
                        <div class="p-3 border rounded bg-light text-center">
                            @if ($stockRequest->store_payment_proof)
                                <a href="{{ asset('storage/'.$stockRequest->store_payment_proof) }}" target="_blank"
                                    class="btn btn-sm btn-outline-info">
                                    <i class="mdi mdi-eye me-1"></i> View Store Proof
                                </a>
                            @else
                                <span class="text-danger small"><i class="mdi mdi-alert-circle me-1"></i> No payment proof uploaded by store.</span>
                            @endif
                        </div>
                    </div>

                    @if ($stockRequest->store_remarks)
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">STORE REMARKS</label>
                            <div class="p-2 border rounded bg-white small text-muted">
                                {{ $stockRequest->store_remarks }}
                            </div>
                        </div>
                    @endif

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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const maxQty = {{ min($stockRequest->pending_quantity, $totalStock) }};

        function toggleRejectSection() {
            const section = document.getElementById('rejectSection');
            section.classList.toggle('d-none');
        }

        function openVerifyModal(id) {
            document.getElementById('verify_req_id').value = id;
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

            const qtyInput = document.getElementById('dispatch_qty');
            const qty = parseInt(qtyInput.value);
            const errorDiv = document.getElementById('qty-error');

            qtyInput.classList.remove('is-invalid');
            if (errorDiv) errorDiv.style.display = 'none';

            if (isNaN(qty) || qty <= 0 || qty > maxQty) {
                qtyInput.classList.add('is-invalid');
                if (errorDiv) {
                    errorDiv.textContent = `Quantity must be between 1 and ${maxQty}.`;
                    errorDiv.style.display = 'block';
                }
                return;
            }

            submitAjaxForm("{{ route('warehouse.stock-requests.change-status') }}", this);
        });

        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitAjaxForm("{{ route('warehouse.stock-requests.change-status') }}", this);
        });

        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitAjaxForm("{{ route('warehouse.stock-requests.verify-payment') }}", this);
        });
    </script>
    @endpush
</x-app-layout>