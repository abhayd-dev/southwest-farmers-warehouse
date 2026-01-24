<x-app-layout title="Recall Request #{{ $recall->id }}">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Recall Request #' . $recall->id])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="mdi mdi-undo-variant text-warning me-2"></i> 
                Recall Request #{{ $recall->id }}
            </h4>
            <p class="text-muted mb-0">
                {{ $recall->store->store_name }} → Warehouse | 
                {{ $recall->product->product_name }} (SKU: {{ $recall->product->sku ?? 'N/A' }})
            </p>
        </div>
        <a href="{{ route('warehouse.stock-control.recall') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row g-4">
        <!-- Left: Summary & Status Timeline -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Request Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline list-unstyled">
                        <li class="timeline-item {{ $recall->status != 'pending_store_approval' ? 'completed' : '' }}">
                            <div class="timeline-badge bg-warning"></div>
                            <div class="timeline-panel">
                                <h6 class="mb-0">Initiated</h6>
                                <small>{{ $recall->created_at->format('d M Y H:i') }} by {{ $recall->initiator->name ?? 'System' }}</small>
                            </div>
                        </li>
                        <li class="timeline-item {{ in_array($recall->status, ['approved_by_store', 'partial_approved', 'dispatched', 'received', 'completed']) ? 'completed' : '' }}">
                            <div class="timeline-badge bg-info"></div>
                            <div class="timeline-panel">
                                <h6 class="mb-0">Store Approval</h6>
                                <small>
                                    @if($recall->approved_by_store_user_id)
                                        {{ $recall->approved_at ? $recall->approved_at->format('d M Y H:i') : 'Approved' }}
                                        by {{ $recall->storeApprover->name ?? 'Store Admin' }}
                                    @else
                                        Pending
                                    @endif
                                </small>
                            </div>
                        </li>
                        <li class="timeline-item {{ in_array($recall->status, ['dispatched', 'received', 'completed']) ? 'completed' : '' }}">
                            <div class="timeline-badge bg-primary"></div>
                            <div class="timeline-panel">
                                <h6 class="mb-0">Dispatched</h6>
                                <small>
                                    @if($recall->dispatched_quantity > 0)
                                        {{ $recall->dispatched_quantity }} units
                                    @else
                                        Pending
                                    @endif
                                </small>
                            </div>
                        </li>
                        <li class="timeline-item {{ $recall->status == 'completed' ? 'completed' : '' }}">
                            <div class="timeline-badge bg-success"></div>
                            <div class="timeline-panel">
                                <h6 class="mb-0">Completed</h6>
                                <small>
                                    @if($recall->received_quantity > 0)
                                        Received {{ $recall->received_quantity }} units
                                    @else
                                        Pending Receipt
                                    @endif
                                </small>
                            </div>
                        </li>
                    </ul>

                    <div class="mt-4">
                        <h6>Summary</h6>
                        <p><strong>Requested:</strong> {{ $recall->requested_quantity }}</p>
                        <p><strong>Approved:</strong> {{ $recall->approved_quantity }}</p>
                        <p><strong>Dispatched:</strong> {{ $recall->dispatched_quantity }}</p>
                        <p><strong>Received:</strong> {{ $recall->received_quantity }}</p>
                        <p><strong>Reason:</strong> {{ ucwords(str_replace('_', ' ', $recall->reason)) }}</p>
                        @if($recall->reason_remarks)
                            <p><strong>Remarks:</strong> {{ $recall->reason_remarks }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Action Area -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        @if($recall->status == 'pending_store_approval')
                            Awaiting Store Approval
                        @elseif(in_array($recall->status, ['approved_by_store', 'partial_approved']))
                            Dispatch Stock from Store
                        @elseif($recall->status == 'dispatched')
                            Confirm Receipt in Warehouse
                        @elseif($recall->status == 'completed')
                            Request Completed
                        @else
                            Action Not Required
                        @endif
                    </h5>
                </div>

                <div class="card-body">
                    @if($recall->status == 'pending_store_approval')
                        <div class="alert alert-info mb-0">
                            <i class="mdi mdi-information-outline me-2"></i>
                            This request is pending approval from the store. No action required from warehouse at this time.
                        </div>

                    @elseif(in_array($recall->status, ['approved_by_store', 'partial_approved']))
                        <div class="alert alert-warning mb-3">
                            <i class="mdi mdi-alert-outline me-2"></i>
                            Store has approved {{ $recall->approved_quantity }} units. Dispatch from store inventory.
                        </div>

                        <form action="{{ route('warehouse.stock-control.recall.dispatch', $recall) }}" method="POST" class="needs-validation" novalidate id="dispatchForm">
                            @csrf

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold">Select Batches (FIFO - Oldest First)</label>
                                    <div>
                                        <small class="text-muted me-3">Total Selected: <span id="totalDispatched">0</span></small>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">Clear All</button>
                                    </div>
                                </div>

                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="width:40px"><input type="checkbox" id="selectAll"></th>
                                                <th>Batch No</th>
                                                <th>Mfg Date</th>
                                                <th>Expiry Date</th>
                                                <th>Days Left</th>
                                                <th>Available</th>
                                                <th>Dispatch Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $batches = \App\Models\ProductBatch::where('product_id', $recall->product_id)
                                                    ->where('warehouse_id', 1)
                                                    ->where('quantity', '>', 0)  // ← FIXED: changed remaining_quantity → quantity
                                                    ->orderBy('expiry_date', 'asc')
                                                    ->get();
                                            @endphp

                                            @forelse($batches as $batch)
                                                @php
                                                    $daysLeft = $batch->expiry_date ? Carbon::parse($batch->expiry_date)->diffInDays(Carbon::today(), false) : null;
                                                    $isExpired = $daysLeft !== null && $daysLeft <= 0;
                                                    $isNearExpiry = $daysLeft !== null && $daysLeft > 0 && $daysLeft <= 30;
                                                @endphp
                                                <tr class="{{ $isExpired ? 'table-danger' : ($isNearExpiry ? 'table-warning' : '') }}">
                                                    <td>
                                                        <input type="checkbox" name="batches[{{ $batch->id }}][selected]" 
                                                               class="batch-check" value="1" 
                                                               data-batch-id="{{ $batch->id }}"
                                                               data-qty-available="{{ $batch->quantity }}"  <!-- ← FIXED: use quantity -->
                                                               data-expiry-days="{{ $daysLeft ?? 'N/A' }}">
                                                    </td>
                                                    <td>{{ $batch->batch_number }}</td>
                                                    <td>{{ $batch->mfg_date ? $batch->mfg_date->format('d M Y') : '-' }}</td>
                                                    <td>{{ $batch->expiry_date ? $batch->expiry_date->format('d M Y') : '-' }}</td>
                                                    <td class="{{ $isExpired ? 'text-danger fw-bold' : ($isNearExpiry ? 'text-warning fw-bold' : '') }}">
                                                        {{ $daysLeft === null ? 'N/A' : ($daysLeft <= 0 ? 'Expired' : $daysLeft . ' days') }}
                                                    </td>
                                                    <td class="text-center">{{ $batch->quantity }}</td>  <!-- ← FIXED: use quantity -->
                                                    <td>
                                                        <input type="number" name="batches[{{ $batch->id }}][quantity]" 
                                                               class="form-control form-control-sm batch-qty-input" 
                                                               min="0" max="{{ $batch->quantity }}" value="0" disabled>
                                                        <div class="invalid-feedback d-block">Exceeds available</div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-3 text-muted">No available batches found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-3">
                                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Cancel</button>
                                <button type="submit" class="btn btn-success" id="dispatchBtn" disabled>
                                    <i class="mdi mdi-truck-fast me-1"></i> Dispatch Selected
                                </button>
                            </div>
                        </form>

                    @elseif($recall->status == 'dispatched')
                        <div class="alert alert-info mb-3">
                            <i class="mdi mdi-truck-fast me-2"></i>
                            Store dispatched {{ $recall->dispatched_quantity }} units. Confirm receipt in warehouse.
                        </div>

                        <form action="{{ route('warehouse.stock-control.recall.receive', $recall) }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Received Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="received_quantity" class="form-control" 
                                       value="{{ $recall->dispatched_quantity }}" min="1" 
                                       max="{{ $recall->dispatched_quantity }}" required>
                                <div class="invalid-feedback">Enter valid quantity (1–{{ $recall->dispatched_quantity }}).</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Warehouse Remarks (Optional)</label>
                                <textarea name="warehouse_remarks" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check-circle me-1"></i> Confirm Receipt
                                </button>
                            </div>
                        </form>

                    @elseif($recall->status == 'completed')
                        <div class="alert alert-success text-center py-5">
                            <i class="mdi mdi-check-circle-outline fs-1 d-block mb-3"></i>
                            <h5 class="mb-2">Recall Completed Successfully</h5>
                            <p class="mb-0">Received {{ $recall->received_quantity }} units in warehouse.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Batch selection logic with live total + validation
$('.batch-check').on('change', function() {
    let qtyInput = $(this).closest('tr').find('.batch-qty-input');
    qtyInput.prop('disabled', !this.checked);
    if (!this.checked) qtyInput.val(0);
    updateTotal();
});

$('.batch-qty-input').on('input', function() {
    let val = parseInt($(this).val()) || 0;
    let max = parseInt($(this).attr('max')) || 0;
    if (val > max) $(this).val(max);
    if (val < 0) $(this).val(0);
    updateTotal();
});

$('#selectAll').on('change', function() {
    $('.batch-check').prop('checked', this.checked).trigger('change');
});

$('#clearSelection').on('click', function() {
    $('.batch-check').prop('checked', false).trigger('change');
});

function updateTotal() {
    let total = 0;
    $('.batch-check:checked').each(function() {
        let qty = parseInt($(this).closest('tr').find('.batch-qty-input').val()) || 0;
        total += qty;
    });
    $('#totalSelectedQty').text(total);
    $('#dispatchBtn').prop('disabled', total === 0);
}

// Form validation
document.querySelectorAll('.needs-validation').forEach(form => {
    form.addEventListener('submit', e => {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
});
</script>
@endpush

@push('styles')
<style>
    .batch-qty-input:invalid { border-color: #dc3545; }
    .table-danger .batch-qty-input { background-color: #f8d7da; }
    .table-warning .batch-qty-input { background-color: #fff3cd; }
</style>
@endpush

</x-app-layout>