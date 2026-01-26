<x-app-layout title="Expiry & Damage Report">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Expiry & Damage Report'])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="mdi mdi-timer-alert text-danger me-2"></i> Expiry & Damage Report
        </h4>
        <div>
            <button class="btn btn-outline-danger">
                <i class="mdi mdi-export"></i> Export Report
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Expiry Within <span class="text-danger">*</span></label>
                        <select id="expiryDays" class="form-select" required>
                            <option value="30">Next 30 Days</option>
                            <option value="60" selected>Next 60 Days</option>
                            <option value="90">Next 90 Days</option>
                            <option value="all">All Upcoming & Expired</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="categoryFilter" class="form-select" required>
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="showDamagedOnly">
                            <label class="form-check-label" for="showDamagedOnly">
                                Show Damaged Only
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="applyFilters" class="btn btn-primary w-100">
                            <i class="mdi mdi-filter me-1"></i> Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table id="expiryDamageTable" class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>SKU / Barcode</th>
                        <th>Category</th>
                        <th>Batch No</th>
                        <th>Mfg Date</th>
                        <th>Expiry Date</th>
                        <th>Days Left</th>
                        <th>Qty Available</th>
                        <th>Damaged</th>
                        <th>Cost Value ($)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    const table = $('#expiryDamageTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("warehouse.stock-control.expiry.data") }}',
            data: function(d) {
                d.days = $('#expiryDays').val();
                d.category_id = $('#categoryFilter').val();
                d.damaged_only = $('#showDamagedOnly').is(':checked') ? 1 : 0;
            },
            error: function(xhr) {
                $('#expiryDamageTable').html(
                    '<tr><td colspan="12" class="text-center py-5 text-danger">' +
                    '<i class="mdi mdi-alert-circle-outline fs-2 mb-3 d-block"></i>' +
                    '<strong>Data Load Failed</strong><br>' +
                    'Server responded with error ' + xhr.status + '. Please try again.' +
                    '</td></tr>'
                );
            }
        },
        columns: [
            { data: 'product_name', name: 'product_name' },
            { data: 'sku', render: (data, t, row) => `${data || '-'}<br><small class="text-muted">${row.barcode || '-'}</small>` },
            { data: 'category_name', defaultContent: '-' },
            { data: 'batch_number' },
            { data: 'mfg_date' },
            { data: 'expiry_date' },
            { data: 'days_left' },
            { data: 'remaining_quantity' },
            { data: 'damaged_quantity' },
            { data: 'value', render: data => '$ ' + (data || 0).toLocaleString('en-IN') },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'asc']],
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });

    $('#applyFilters, #expiryDays, #categoryFilter, #showDamagedOnly').on('change click', () => table.draw());
});
</script>
@endpush

</x-app-layout>