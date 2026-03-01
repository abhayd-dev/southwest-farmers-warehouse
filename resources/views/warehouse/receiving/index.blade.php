<x-app-layout title="Receiving Orders">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
            <h4 class="fw-bold mb-0 text-dark"><i class="mdi mdi-truck-check me-2 text-primary"></i>Receiving Orders</h4>
        </div>

        {{-- FILTERS --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">PO Number</label>
                        <input type="text" class="form-control form-control-sm" id="filter_po"
                            placeholder="e.g. PO-123">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select class="form-select form-select-sm" id="filter_status">
                            <option value="all">All</option>
                            <option value="ordered">Ordered</option>
                            <option value="partial">In Transit</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Vendor Name</label>
                        <input type="text" class="form-control form-control-sm" id="filter_vendor"
                            placeholder="Search Vendor...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Date Range</label>
                        <div class="input-group input-group-sm">
                            <input type="date" class="form-control" id="filter_date_from">
                            <span class="input-group-text bg-light border-0">to</span>
                            <input type="date" class="form-control" id="filter_date_to">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex flex-row gap-2">
                        <button type="button" class="btn btn-sm btn-primary w-100" id="btnFilter">Filter</button>
                        <button type="button" class="btn btn-sm btn-light w-100" id="btnReset">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-top-left-radius: 0;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="receiving-table" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="px-4 py-3">PO Number</th>
                                <th class="py-3">Vendor</th>
                                <th class="py-3">PO Date</th>
                                <th class="py-3">Receiving Date</th>
                                <th class="py-3">PO Amount</th>
                                <th class="py-3">Receiving Amount</th>
                                <th class="py-3" style="width: 200px;">Receiving Progress</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-end px-4">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                var table = $('#receiving-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('warehouse.receiving.index') }}",
                        data: function(d) {
                            d.status = $('#filter_status').val();
                            d.po_number = $('#filter_po').val();
                            d.vendor = $('#filter_vendor').val();
                            d.date_from = $('#filter_date_from').val();
                            d.date_to = $('#filter_date_to').val();
                        }
                    },
                    columns: [{
                            data: 'po_number',
                            name: 'po_number',
                            className: 'fw-bold px-4'
                        },
                        {
                            data: 'vendor_name',
                            name: 'vendor.name'
                        },
                        {
                            data: 'order_date',
                            name: 'order_date'
                        },
                        {
                            data: 'receiving_date',
                            name: 'updated_at'
                        },
                        {
                            data: 'po_amount',
                            name: 'total_amount'
                        },
                        {
                            data: 'receiving_amount',
                            name: 'receiving_amount',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'progress',
                            name: 'progress',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'status_badge',
                            name: 'status'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-end px-4'
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ],
                });

                $('#btnFilter').on('click', function() {
                    table.ajax.reload();
                });
                $('#btnReset').on('click', function() {
                    $('#filterForm')[0].reset();
                    table.ajax.reload();
                });
            });
        </script>
    @endpush
</x-app-layout>
