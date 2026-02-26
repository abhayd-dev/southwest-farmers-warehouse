<x-app-layout title="Store Order - {{ $storeOrder->po_number }}">

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">
                            <i class="mdi mdi-store text-primary"></i>
                            Store Order: {{ $storeOrder->po_number }}
                        </h4>
                        <p class="text-muted mb-0 small">
                            Store: <strong>{{ $storeOrder->store->store_name }}</strong> &bull;
                            Date: {{ $storeOrder->request_date->format('d M Y') }}
                            @if ($storeOrder->approver)
                                &bull; Approved by: {{ $storeOrder->approver->name ?? 'N/A' }}
                            @endif
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        @php
                            $badges = [
                                'pending' => 'bg-warning text-dark',
                                'approved' => 'bg-primary',
                                'dispatched' => 'bg-info',
                                'completed' => 'bg-success',
                                'rejected' => 'bg-danger',
                            ];
                        @endphp
                        <span class="badge {{ $badges[$storeOrder->status] ?? 'bg-secondary' }} fs-6 px-3 py-2">
                            {{ strtoupper($storeOrder->status) }}
                        </span>
                        <a href="{{ route('warehouse.store-orders.index') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="mdi mdi-alert-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">

            {{-- ITEMS TABLE --}}
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm">
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            <i class="mdi mdi-package-variant text-primary"></i>
                            Order Items
                        </h5>
                        <span class="badge bg-secondary">{{ $storeOrder->items->count() }} items</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-3 py-3 text-muted fw-semibold small">#</th>
                                        <th class="py-3 text-muted fw-semibold small">UPC</th>
                                        <th class="py-3 text-muted fw-semibold small">Product</th>
                                        <th class="py-3 text-muted fw-semibold small text-center">Req. Qty</th>
                                        <th class="py-3 text-muted fw-semibold small text-center">Sent</th>
                                        <th class="py-3 text-muted fw-semibold small text-center">Pending</th>
                                        <th class="py-3 text-muted fw-semibold small text-center">Warehouse Stock</th>
                                        <th class="py-3 text-muted fw-semibold small text-center">Status</th>
                                        @if ($storeOrder->status === 'pending')
                                            <th class="px-3 py-3 text-muted fw-semibold small text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($storeOrder->items as $index => $item)
                                        <tr class="border-bottom">
                                            <td class="px-3 py-3 text-muted">{{ $index + 1 }}</td>
                                            <td class="py-3">
                                                <code class="text-dark">{{ $item->product->barcode ?? 'N/A' }}</code>
                                            </td>
                                            <td class="py-3">
                                                <div class="fw-semibold text-dark">{{ $item->product->product_name }}
                                                </div>
                                                <small class="text-muted">{{ $item->product->sku }}</small>
                                            </td>
                                            <td class="py-3 text-center fw-bold">{{ $item->requested_qty }}</td>
                                            <td class="py-3 text-center text-success fw-bold">
                                                {{ $item->dispatched_qty }}</td>
                                            <td class="py-3 text-center text-warning fw-bold">{{ $item->pending_qty }}
                                            </td>
                                            <td class="py-3 text-center">
                                                @php
                                                    $wQty = $item->warehouse_qty ?? 0;
                                                    $minL = $item->min_level ?? 0;
                                                    $stockColor =
                                                        $wQty <= 0
                                                            ? 'danger'
                                                            : ($wQty <= $minL
                                                                ? 'warning'
                                                                : 'success');
                                                @endphp
                                                <span class="badge bg-{{ $stockColor }}">{{ $wQty }}</span>
                                                @if ($item->in_transit_qty > 0)
                                                    <br><small class="text-info">+{{ $item->in_transit_qty }} in
                                                        transit</small>
                                                @endif
                                            </td>
                                            <td class="py-3 text-center">
                                                @php
                                                    $itemBadges = [
                                                        'pending' => 'bg-warning text-dark',
                                                        'approved' => 'bg-primary',
                                                        'dispatched' => 'bg-info',
                                                        'rejected' => 'bg-danger',
                                                    ];
                                                @endphp
                                                <span class="badge {{ $itemBadges[$item->status] ?? 'bg-secondary' }}">
                                                    {{ strtoupper($item->status) }}
                                                </span>
                                                @if ($item->rejection_reason)
                                                    <br><small class="text-muted">{{ $item->rejection_reason }}</small>
                                                @endif
                                            </td>
                                            @if ($storeOrder->status === 'pending')
                                                <td class="px-3 py-3 text-center">
                                                    @if ($item->status === 'pending')
                                                        <button class="btn btn-sm btn-outline-success me-1"
                                                            onclick="openApproveItem({{ $item->id }}, {{ $item->requested_qty }}, {{ $item->warehouse_qty ?? 0 }})">
                                                            <i class="mdi mdi-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            onclick="openRejectItem({{ $item->id }})">
                                                            <i class="mdi mdi-close"></i>
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">Done</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- BULK ACTIONS --}}
                    @if ($storeOrder->status === 'pending')
                        <div class="card-footer bg-white border-top py-3 d-flex gap-2 justify-content-end">
                            <form method="POST"
                                action="{{ route('warehouse.store-orders.approve', $storeOrder->id) }}"
                                id="approveAllForm">
                                @csrf
                                <input type="hidden" name="admin_note" id="bulk_admin_note" value="">
                                <button type="button" class="btn btn-success" onclick="confirmApproveAll()">
                                    <i class="mdi mdi-check-all me-1"></i> Approve All (Auto-Ration)
                                </button>
                            </form>
                            <button class="btn btn-danger" onclick="openRejectPO()">
                                <i class="mdi mdi-close-circle me-1"></i> Reject PO
                            </button>
                        </div>
                    @endif

                    @if ($storeOrder->status === 'approved')
                        <div class="card-footer bg-white border-top py-3 d-flex gap-2 justify-content-end">
                            <form method="POST"
                                action="{{ route('warehouse.store-orders.auto-arrange', $storeOrder->id) }}"
                                id="autoArrangeForm">
                                @csrf
                                <button type="button" class="btn btn-warning" onclick="confirmAutoArrange()">
                                    <i class="mdi mdi-package-variant-closed me-1"></i> Auto Arrange Pallets
                                </button>
                            </form>
                            <form method="POST"
                                action="{{ route('warehouse.store-orders.dispatch', $storeOrder->id) }}"
                                id="dispatchForm">
                                @csrf
                                <button type="button" class="btn btn-primary" onclick="confirmDispatch()">
                                    <i class="mdi mdi-truck me-1"></i> Dispatch Items
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($storeOrder->status === 'approved')
                        <div class="card-footer bg-white border-top py-3 d-flex gap-2 justify-content-end">
                            <form method="POST"
                                action="{{ route('warehouse.store-orders.dispatch', $storeOrder->id) }}"
                                id="dispatchForm">
                                @csrf
                                <button type="button" class="btn btn-primary" onclick="confirmDispatch()">
                                    <i class="mdi mdi-truck me-1"></i> Dispatch Items
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($storeOrder->status === 'dispatched')
                        <div class="card-footer bg-white border-top py-3 d-flex gap-2 justify-content-end">
                            <form method="POST"
                                action="{{ route('warehouse.store-orders.complete', $storeOrder->id) }}"
                                id="completeForm">
                                @csrf
                                <button type="button" class="btn btn-success" onclick="confirmComplete()">
                                    <i class="mdi mdi-check-circle me-1"></i> Mark as Completed
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="col-lg-3">

                {{-- Admin Note --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="mdi mdi-note-text text-warning"></i> Admin Note
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($storeOrder->admin_note)
                            <p class="mb-0 text-dark">{{ $storeOrder->admin_note }}</p>
                        @else
                            <p class="text-muted mb-0 small">No admin note.</p>
                        @endif

                        @if (in_array($storeOrder->status, ['pending', 'approved']))
                            <form method="POST" action="{{ route('warehouse.store-orders.note', $storeOrder->id) }}"
                                class="mt-3">
                                @csrf
                                @method('PATCH')
                                <textarea name="admin_note" class="form-control form-control-sm mb-2" rows="3" placeholder="Add a note...">{{ $storeOrder->admin_note }}</textarea>
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">Save Note</button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- PO Info --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="mdi mdi-information text-primary"></i> PO Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted small">PO Number</td>
                                <td class="fw-bold small">{{ $storeOrder->po_number }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Store</td>
                                <td class="fw-semibold small">{{ $storeOrder->store->store_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Request Date</td>
                                <td class="small">{{ $storeOrder->request_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small">Created</td>
                                <td class="small">{{ $storeOrder->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @if ($storeOrder->approved_at)
                                <tr>
                                    <td class="text-muted small">Approved At</td>
                                    <td class="small">{{ $storeOrder->approved_at->format('d M Y H:i') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted small">Total Items</td>
                                <td class="fw-bold small">{{ $storeOrder->items->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Approve Item Modal --}}
    <div class="modal fade" id="approveItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Warehouse Stock: <strong id="ai_warehouse_qty">-</strong> &bull;
                        Requested: <strong id="ai_requested_qty">-</strong>
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Approve <span class="text-danger">*</span></label>
                        <input type="number" id="ai_qty" class="form-control" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="submitApproveItem()">Approve</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Item Modal --}}
    <div class="modal fade" id="rejectItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea id="ri_reason" class="form-control" rows="3" placeholder="Reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitRejectItem()">Reject</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject PO Modal --}}
    <div class="modal fade" id="rejectPOModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('warehouse.store-orders.reject', $storeOrder->id) }}"
                class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Entire PO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject PO</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            let currentItemId = null;

            function openApproveItem(itemId, requestedQty, warehouseQty) {
                currentItemId = itemId;
                document.getElementById('ai_requested_qty').textContent = requestedQty;
                document.getElementById('ai_warehouse_qty').textContent = warehouseQty;
                document.getElementById('ai_qty').value = Math.min(requestedQty, warehouseQty);
                document.getElementById('ai_qty').max = Math.min(requestedQty, warehouseQty);
                new bootstrap.Modal(document.getElementById('approveItemModal')).show();
            }

            function openRejectItem(itemId) {
                currentItemId = itemId;
                document.getElementById('ri_reason').value = '';
                new bootstrap.Modal(document.getElementById('rejectItemModal')).show();
            }

            function openRejectPO() {
                new bootstrap.Modal(document.getElementById('rejectPOModal')).show();
            }

            function submitApproveItem() {
                const qty = document.getElementById('ai_qty').value;
                if (!qty || qty < 1) {
                    Swal.fire('Error', 'Please enter a valid quantity.', 'error');
                    return;
                }
                ajaxPost(`{{ route('warehouse.store-orders.item.approve', ':id') }}`.replace(':id', currentItemId), {
                    dispatch_qty: qty
                });
            }

            function submitRejectItem() {
                const reason = document.getElementById('ri_reason').value.trim();
                if (!reason) {
                    Swal.fire('Error', 'Please enter a rejection reason.', 'error');
                    return;
                }
                ajaxPost(`{{ route('warehouse.store-orders.item.reject', ':id') }}`.replace(':id', currentItemId), {
                    reason
                });
            }

            function ajaxPost(url, data) {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                Object.entries(data).forEach(([k, v]) => formData.append(k, v));

                fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                    icon: 'success',
                                    title: 'Done',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                })
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Network error.', 'error'));
            }

            function confirmApproveAll() {
                Swal.fire({
                    title: 'Approve Entire PO?',
                    text: "Rationing rules will be applied automatically.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b759',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Approve All'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('approveAllForm').submit();
                    }
                });
            }

            function confirmDispatch() {
                Swal.fire({
                    title: 'Dispatch Items?',
                    text: "This will deduct stock from the warehouse.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Dispatch'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('dispatchForm').submit();
                    }
                });
            }

            function confirmComplete() {
                Swal.fire({
                    title: 'Mark as Completed?',
                    text: "This confirms the store has received the items.",
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#10b759',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Complete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('completeForm').submit();
                    }
                });
            }
        </script>
    @endpush

</x-app-layout>
