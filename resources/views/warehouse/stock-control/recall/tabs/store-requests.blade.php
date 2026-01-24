<div class="mb-4">
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label">Date From</label>
            <input type="date" class="form-control store-date-filter" data-filter="date_from">
        </div>
        <div class="col-md-2">
            <label class="form-label">Date To</label>
            <input type="date" class="form-control store-date-filter" data-filter="date_to">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select store-filter" data-filter="status">
                <option value="">All Status</option>
                <option value="pending_store_approval">Pending</option>
                <option value="approved_by_store">Approved</option>
                <option value="dispatched">Dispatched</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Store</label>
            <select class="form-select store-filter" data-filter="store_id">
                <option value="">All Stores</option>
                @foreach(\App\Models\StoreDetail::active()->get() as $store)
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

<table id="storeRequestsTable" class="table table-hover table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Store</th>
            <th>Product</th>
            <th>Requested Qty</th>
            <th>Approved Qty</th>
            <th>Status</th>
            <th>Requested By</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>

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
            { data: 'id', render: d => '#' + String(d).padStart(5, '0') },
            { data: 'store_name' },
            { data: 'product_name' },
            { data: 'requested_quantity', className: 'text-center' },
            { data: 'approved_quantity', className: 'text-center' },
            { data: 'status_badge', searchable: false, orderable: false },
            { data: 'initiator.name', defaultContent: '-' },
            { data: 'action', searchable: false, orderable: false }
        ]
    });

    $('#applyStoreFilters').click(() => table.draw());
});
</script>
@endpush