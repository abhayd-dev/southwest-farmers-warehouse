<x-app-layout title="Process Request #{{ $stockRequest->id }}">
    <div class="container-fluid">
        
        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('warehouse.stock-requests.index') }}" class="text-decoration-none">
                                        Requests
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">#REQ-{{ $stockRequest->id }}</li>
                            </ol>
                        </nav>
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-truck-fast text-primary"></i> Dispatch Stock
                        </h4>
                    </div>

                    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-md-end">
                        <button class="btn btn-success shadow-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#stockInModal">
                            <i class="mdi mdi-plus-box me-1"></i> Stock In
                        </button>

                        <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-outline-secondary shadow-sm flex-fill flex-md-grow-0">
                            <i class="mdi mdi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- LEFT COLUMN: INFO & WAREHOUSE AVAILABILITY --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold text-muted text-uppercase small">Request Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Store Details</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="mdi mdi-store text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $stockRequest->store->store_name }}</h6>
                                    <small class="text-muted">{{ $stockRequest->store->city }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Product Details</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="mdi mdi-box-seam text-info fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $stockRequest->product->product_name }}</h6>
                                    <small class="text-muted">SKU: {{ $stockRequest->product->sku }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="border-light my-4">

                        <div class="row text-center g-0">
                            <div class="col-4 border-end">
                                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Requested</small>
                                <h5 class="text-primary fw-bold mb-0" id="requested-qty">
                                    {{ $stockRequest->requested_quantity }}
                                </h5>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Sent</small>
                                <h5 class="text-success fw-bold mb-0">
                                    {{ $stockRequest->fulfilled_quantity ?? 0 }}
                                </h5>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Pending</small>
                                <h5 class="text-danger fw-bold mb-0" id="pending-qty">
                                    {{ $stockRequest->pending_quantity }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-info bg-opacity-10 mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-info fw-bold mb-3 d-flex align-items-center">
                            <i class="mdi mdi-warehouse me-2 fs-5"></i> Warehouse Availability
                        </h6>
                        @php $totalStock = $stockRequest->product->batches->sum('quantity'); @endphp

                        <div class="d-flex align-items-baseline mb-3">
                            <h2 class="fw-bold mb-0 {{ $totalStock < $stockRequest->pending_quantity ? 'text-danger' : 'text-dark' }}">
                                {{ $totalStock }}
                            </h2>
                            <small class="text-muted ms-2 fw-semibold">{{ $stockRequest->product->unit ?? 'Units' }} available</small>
                        </div>

                        <div>
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">FIFO Batches (Auto-Selected)</small>
                            <ul class="list-group list-group-flush bg-transparent mt-2">
                                @forelse($stockRequest->product->batches->take(3) as $batch)
                                    <li class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 border-bottom border-info border-opacity-25">
                                        <div>
                                            <span class="small fw-bold text-dark d-block">{{ $batch->batch_number }}</span>
                                            <span class="small text-muted" style="font-size: 0.7rem;">Exp: {{ $batch->expiry_date ? $batch->expiry_date->format('M Y') : 'N/A' }}</span>
                                        </div>
                                        <span class="badge bg-white text-info border border-info border-opacity-25 align-self-center">{{ $batch->quantity }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item bg-transparent text-danger small fst-italic px-0">No active batches found!</li>
                                @endforelse
                                @if ($stockRequest->product->batches->count() > 3)
                                    <li class="list-group-item bg-transparent text-center small text-muted py-2 fst-italic">
                                        +{{ $stockRequest->product->batches->count() - 3 }} more batches
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: ACTION & DISPATCH DETAILS --}}
            <div class="col-lg-8">
                
                {{-- 1. ACTION CARD (Pending Form OR Status Card) --}}
                <div class="card border-0 shadow-sm mb-4 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold text-dark">Process Dispatch</h6>
                    </div>
                    <div class="card-body p-4">
                        @if ($stockRequest->pending_quantity > 0 && $stockRequest->status !== 'rejected')
                            {{-- DISPATCH FORM --}}
                            <form id="dispatchForm" class="needs-validation" novalidate method="POST">
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
                                        <span class="input-group-text bg-light text-muted fw-bold">{{ $stockRequest->product->unit ?? 'Units' }}</span>
                                        <div class="invalid-feedback fw-bold" id="qty-error">
                                            Please enter a valid quantity (1 - {{ min($stockRequest->pending_quantity, $totalStock) }}).
                                        </div>
                                    </div>
                                    <div class="form-text text-muted mt-2 d-flex align-items-start gap-2">
                                        <i class="mdi mdi-information-outline mt-1"></i>
                                        <span>
                                            System will automatically deduct from the oldest batches first (FIFO).<br>
                                            Max dispatchable: <strong class="text-dark">{{ min($stockRequest->pending_quantity, $totalStock) }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Admin Note <span class="text-muted fw-normal">(Optional)</span></label>
                                    <textarea name="admin_note" class="form-control" rows="3" placeholder="Add a message for the store manager..."></textarea>
                                </div>

                                <div class="d-flex flex-column flex-md-row gap-3 pt-2">
                                    <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm flex-fill">
                                        <i class="mdi mdi-truck-fast me-2"></i> Approve & Dispatch
                                    </button>

                                    <button type="button" class="btn btn-outline-danger btn-lg px-4 flex-fill"
                                        onclick="toggleRejectSection()">
                                        <i class="mdi mdi-close-circle me-1"></i> Reject
                                    </button>
                                </div>
                            </form>

                            {{-- REJECT FORM (Hidden) --}}
                            <div id="rejectSection"
                                class="mt-4 p-4 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25 d-none">
                                <form id="rejectForm" method="POST">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $stockRequest->id }}">
                                    <input type="hidden" name="status" value="rejected">

                                    <h6 class="text-danger fw-bold mb-3"><i class="mdi mdi-alert-circle-outline me-1"></i> Reject Request</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                                        <textarea name="admin_note" class="form-control" rows="3"
                                            placeholder="Why are you rejecting this request?" required></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light border"
                                            onclick="toggleRejectSection()">Cancel</button>
                                        <button type="submit" class="btn btn-danger px-4">Confirm Rejection</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            {{-- STATUS CARDS (Rejected / Dispatched / Completed) --}}
                            <div class="text-center py-5">
                                @if ($stockRequest->status == 'rejected')
                                    <div class="mb-3">
                                        <div class="avatar-lg mx-auto bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="mdi mdi-close-circle text-danger display-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="fw-bold text-danger">Request Rejected</h4>
                                    <p class="text-muted mx-auto" style="max-width: 400px;">Reason: {{ $stockRequest->admin_note ?? 'N/A' }}</p>

                                @elseif($stockRequest->status == 'dispatched')
                                    <div class="mb-3">
                                        <div class="avatar-lg mx-auto bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="mdi mdi-truck-check text-info display-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="fw-bold text-info">Dispatched & Pending Payment</h4>
                                    <p class="text-muted">Items dispatched. Waiting for payment verification.</p>
                                    <button class="btn btn-primary mt-3 px-4 shadow-sm" onclick="openVerifyModal({{ $stockRequest->id }})">
                                        <i class="mdi mdi-check-decagram me-1"></i> Verify Payment Now
                                    </button>

                                @else
                                    <div class="mb-3">
                                        <div class="avatar-lg mx-auto bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="mdi mdi-check-decagram text-success display-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="fw-bold text-success">Order Completed</h4>
                                    <p class="text-muted">Stock received and payment verified.</p>

                                    {{-- COMPLETED VERIFICATION DETAILS --}}
                                    <div class="mt-4 p-3 bg-light rounded text-start mx-auto border" style="max-width: 400px;">
                                        <h6 class="small fw-bold text-uppercase text-muted border-bottom pb-2 mb-2">Verification Details</h6>
                                        <div class="mb-2 d-flex justify-content-between">
                                            <small class="text-muted fw-bold">Store Proof:</small>
                                            @if ($stockRequest->store_payment_proof)
                                                <a href="{{ Storage::url($stockRequest->store_payment_proof) }}" target="_blank" class="text-primary text-decoration-none small fw-bold">
                                                    <i class="mdi mdi-file-document me-1"></i> View File
                                                </a>
                                            @else
                                                <span class="text-danger small">No proof uploaded.</span>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted fw-bold">Warehouse Proof:</small>
                                            @if ($stockRequest->warehouse_payment_proof)
                                                <a href="{{ Storage::url($stockRequest->warehouse_payment_proof) }}" target="_blank" class="text-primary text-decoration-none small fw-bold">
                                                    <i class="mdi mdi-file-check me-1"></i> View File
                                                </a>
                                            @else
                                                <span class="text-muted small">Pending upload.</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-dark mt-4 px-4 shadow-sm">
                                    <i class="mdi mdi-view-dashboard-outline me-1"></i> Return to Dashboard
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 2. DISPATCHED BATCH DETAILS (Visible if dispatched/completed) --}}
                @if($stockRequest->batch_details && count($stockRequest->batch_details) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold text-dark">
                            <i class="mdi mdi-clipboard-list-outline text-primary me-1"></i> Dispatched Batch Details (FIFO)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="bg-light text-uppercase small text-muted">
                                    <tr>
                                        <th class="ps-4 py-3">Batch Number</th>
                                        <th class="py-3">Expiry Date</th>
                                        <th class="py-3">Cost Price</th>
                                        <th class="text-end pe-4 py-3">Quantity Sent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockRequest->batch_details as $batch)
                                    <tr>
                                        <td class="ps-4 fw-bold font-monospace text-primary">
                                            {{ $batch['batch_number'] ?? 'N/A' }}
                                        </td>
                                        <td>
                                            @if(isset($batch['expiry_date']))
                                                {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d M Y') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ number_format($batch['cost_price'] ?? 0, 2) }}
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-success">
                                            {{ $batch['qty'] ?? 0 }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold py-3 text-uppercase small text-muted">Total Dispatched:</td>
                                        <td class="text-end pe-4 fw-bold py-3 fs-6 text-dark">
                                            {{ collect($stockRequest->batch_details)->sum('qty') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- MODALS --}}
    <div class="modal fade" id="verifyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="verifyForm" class="modal-content border-0 shadow" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="mdi mdi-check-decagram me-2"></i> Verify Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="request_id" id="verify_req_id">
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Store Payment Proof</label>
                        <div class="p-3 border rounded bg-light text-center">
                            @if ($stockRequest->store_payment_proof)
                                <a href="{{ Storage::url($stockRequest->store_payment_proof) }}" target="_blank"
                                    class="btn btn-outline-info shadow-sm">
                                    <i class="mdi mdi-eye me-1"></i> View Store Proof
                                </a>
                            @else
                                <span class="text-danger small fw-bold"><i class="mdi mdi-alert-circle me-1"></i> No payment proof uploaded.</span>
                            @endif
                        </div>
                    </div>

                    @if ($stockRequest->store_remarks)
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Store Remarks</label>
                            <div class="p-3 border rounded bg-white text-muted fst-italic">
                                "{{ $stockRequest->store_remarks }}"
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Warehouse Payment Proof <span class="text-danger">*</span></label>
                        <input type="file" name="warehouse_payment_proof" class="form-control" required accept="image/*,.pdf">
                        <div class="form-text">Upload receipt or confirmation document.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Verification Remarks <span class="text-danger">*</span></label>
                        <textarea name="warehouse_remarks" class="form-control" required rows="2" placeholder="Enter transaction ID or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success px-4">Complete Verification</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="stockInModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="stockInFormShow" class="modal-content border-0 shadow" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="mdi mdi-plus-box me-2"></i> Purchase In (Direct Stock)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->product_name }} ({{ $prod->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" required min="1" placeholder="Enter quantity to add">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Purchase Reference</label>
                        <input type="text" name="purchase_ref" class="form-control" placeholder="e.g. PO-XXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Existing JavaScript logic remains unchanged...
        const maxQty = {{ min($stockRequest->pending_quantity, $totalStock) }};

        function toggleRejectSection() {
            const section = document.getElementById('rejectSection');
            if (section) section.classList.toggle('d-none');
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
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Server returned invalid response');
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
        }

        const rejectForm = document.getElementById('rejectForm');
        if (rejectForm) {
            rejectForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAjaxForm("{{ route('warehouse.stock-requests.change-status') }}", this);
            });
        }

        const verifyForm = document.getElementById('verifyForm');
        if (verifyForm) {
            verifyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAjaxForm("{{ route('warehouse.stock-requests.verify-payment') }}", this);
            });
        }

        const stockInFormShow = document.getElementById('stockInFormShow');
        if (stockInFormShow) {
            stockInFormShow.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAjaxForm("{{ route('warehouse.stock-requests.purchase-in') }}", this);
            });
        }
    </script>
    @endpush
</x-app-layout>