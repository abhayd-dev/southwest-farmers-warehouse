<div class="mb-4">
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <label class="form-label">Date From</label>
            <input type="date" class="form-control expiry-date-filter" data-filter="date_from">
        </div>
        <div class="col-md-2">
            <label class="form-label">Date To</label>
            <input type="date" class="form-control expiry-date-filter" data-filter="date_to">
        </div>
        <div class="col-md-2">
            <label class="form-label">Report Type</label>
            <select class="form-select expiry-filter" data-filter="report_type">
                <option value="">All Reports</option>
                <option value="expiry">Expiry</option>
                <option value="damage">Damage</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Store</label>
            <select class="form-select expiry-filter" data-filter="store_id">
                <option value="">All Stores</option>
                @foreach(\App\Models\StoreDetail::active()->get() as $store)
                    <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary w-100" id="applyExpiryFilters">
                <i class="mdi mdi-filter me-1"></i> Apply Filters
            </button>
        </div>
    </div>
</div>

<table id="expiryDamageTable" class="table table-hover table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>Product</th>
            <th>Batch</th>
            <th>Expiry Date</th>
            <th>Days Left</th>
            <th>Location</th>
            <th>Qty</th>
            <th>Damaged</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>

@push('scripts')
<script>
$(function() {
    let table = $('#expiryDamageTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route('warehouse.stock-control.recall.expiry-damage') }}',
            data: function(d) {
                d.date_from = $('input.expiry-date-filter[data-filter="date_from"]').val();
                d.date_to = $('input.expiry-date-filter[data-filter="date_to"]').val();
                d.report_type = $('select.expiry-filter[data-filter="report_type"]').val();
                d.store_id = $('select.expiry-filter[data-filter="store_id"]').val();
            }
        },
        columns: [
            { data: 'product_name' },
            { data: 'batch_number' },
            { data: 'expiry_date', render: d => d ? new Date(d).toLocaleDateString() : '-' },
            { data: 'days_left', className: 'text-center' },
            { data: 'store_name', defaultContent: 'Warehouse' },
            { data: 'quantity', className: 'text-center' },
            { data: 'damaged_quantity', className: 'text-center' },
            { data: 'status', searchable: false, orderable: false },
            { data: 'action', searchable: false, orderable: false }
        ]
    });

    $('#applyExpiryFilters').click(() => table.draw());
});
</script>
@endpush