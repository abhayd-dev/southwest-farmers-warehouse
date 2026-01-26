<div class="mb-4">
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label small fw-bold">Date From</label>
            <input type="date" class="form-control store-date-filter" data-filter="date_from">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Date To</label>
            <input type="date" class="form-control store-date-filter" data-filter="date_to">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Status</label>
            <select class="form-select store-filter" data-filter="status">
                <option value="">All Status</option>
                {{-- Updated Statuses for Store-Initiated Flow --}}
                <option value="pending_warehouse_approval">Needs Approval</option>
                <option value="approved">Approved (Waiting Dispatch)</option>
                <option value="dispatched">In Transit</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Store</label>
            <select class="form-select store-filter" data-filter="store_id">
                <option value="">All Stores</option>
                @foreach(\App\Models\StoreDetail::where('is_active', true)->get() as $store)
                    <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary w-100" id="applyStoreFilters">
                <i class="mdi mdi-filter me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="storeRequestsTable" class="table table-hover table-bordered align-middle w-100">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Store</th>
                <th>Product</th>
                <th class="text-center">Req. Qty</th>
                <th class="text-center">Appr. Qty</th>
                <th class="text-center">Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
$(function() {
    let table = $('#storeRequestsTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route('warehouse.stock-control.recall.store-requests') }}',
            data: function(d) {
                d.date_from = $('input.store-date-filter[data-filter="date_from"]').val();
                d.date_to = $('input.store-date-filter[data-filter="date_to"]').val();
                d.status = $('select.store-filter[data-filter="status"]').val();
                d.store_id = $('select.store-filter[data-filter="store_id"]').val();
            }
        },
        columns: [
            { data: 'id', render: d => '<span class="fw-bold">#' + String(d).padStart(5, '0') + '</span>' },
            { data: 'store_name' },
            { data: 'product_name' },
            { data: 'requested_quantity', className: 'text-center' },
            { data: 'approved_quantity', className: 'text-center', render: d => d || '-' },
            { data: 'status_badge', searchable: false, orderable: false, className: 'text-center' },
            { data: 'action', searchable: false, orderable: false, className: 'text-end' }
        ],
        order: [[0, 'desc']] // Newest first
    });

    $('#applyStoreFilters').click(() => table.draw());
});
</script>
@endpush