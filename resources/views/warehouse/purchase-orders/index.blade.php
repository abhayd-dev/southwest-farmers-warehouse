<x-app-layout title="Purchase Orders">
    <div class="container-fluid p-3 p-md-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="mdi mdi-cart-arrow-down text-primary me-2"></i>Purchase Orders</h4>
                <small class="text-muted">Manage procurement and receiving</small>
            </div>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_po'))
            <a href="{{ route('warehouse.purchase-orders.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Create PO
            </a>
            @endif
        </div>

        {{-- STAT CARDS --}}
        <div class="row g-3 mb-4" id="po-stats">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-primary" id="stat-total">—</div>
                    <div class="small text-muted">Total POs</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-warning" id="stat-pending">—</div>
                    <div class="small text-muted">Pending Receive</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-success" id="stat-completed">—</div>
                    <div class="small text-muted">Completed</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-info" id="stat-value">—</div>
                    <div class="small text-muted">Total Value</div>
                </div>
            </div>
        </div>

        {{-- STATUS FILTER TABS --}}
        <ul class="nav nav-tabs mb-0" id="statusTabs">
            @foreach(['all' => ['label' => 'All', 'color' => 'dark'],
                      'draft' => ['label' => 'Draft', 'color' => 'secondary'],
                      'ordered' => ['label' => 'Ordered', 'color' => 'info'],
                      'partial' => ['label' => 'Partial', 'color' => 'warning'],
                      'completed' => ['label' => 'Completed', 'color' => 'success'],
                      'cancelled' => ['label' => 'Cancelled', 'color' => 'danger']] as $key => $tab)
                <li class="nav-item">
                    <a href="#" class="nav-link status-tab {{ $key === 'all' ? 'active fw-bold' : '' }}" data-status="{{ $key }}">
                        {{ $tab['label'] }}
                        <span class="badge bg-{{ $tab['color'] }} ms-1 tab-count" id="count-{{ $key }}"></span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="card border-0 shadow-sm" style="border-top-left-radius: 0;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="po-table" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">PO Number</th>
                                <th>Vendor</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Approval</th>
                                <th>Receiving Progress</th>
                                <th>Status</th>
                                <th class="text-end px-4">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentStatus = 'all';

        const table = $('#po-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('warehouse.purchase-orders.index') }}",
                data: function(d) {
                    if (currentStatus !== 'all') d.status = currentStatus;
                }
            },
            columns: [
                {data: 'po_number', name: 'po_number', className: 'fw-bold px-4'},
                {data: 'vendor_name', name: 'vendor.name'},
                {data: 'order_date', name: 'order_date'},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'approval_badge', name: 'approval_status', orderable: false, searchable: false},
                {data: 'progress', name: 'progress', orderable: false, searchable: false},
                {data: 'status_badge', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end px-4'}
            ],
            order: [[0, 'desc']],
            drawCallback: function() {
                loadStats();
            }
        });

        // Tab click handler
        $('.status-tab').on('click', function(e) {
            e.preventDefault();
            $('.status-tab').removeClass('active fw-bold');
            $(this).addClass('active fw-bold');
            currentStatus = $(this).data('status');
            table.ajax.reload();
        });

        // Load summary stats
        function loadStats() {
            $.get("{{ route('warehouse.purchase-orders.index') }}", {stats: 1}, function(data) {
                if (data.stats) {
                    $('#stat-total').text(data.stats.total);
                    $('#stat-pending').text(data.stats.pending);
                    $('#stat-completed').text(data.stats.completed);
                    $('#stat-value').text('$' + data.stats.value);
                    // Update tab counts
                    $.each(data.stats.by_status, function(status, count) {
                        $('#count-' + status).text(count > 0 ? count : '');
                    });
                    $('#count-all').text(data.stats.total > 0 ? data.stats.total : '');
                }
            });
        }

        loadStats();
    </script>
    @endpush
</x-app-layout>