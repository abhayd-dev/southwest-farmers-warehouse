<x-app-layout title="Consolidated Stock Overview">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Consolidated Stock Overview'])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="mdi mdi-view-dashboard-outline text-primary me-2"></i> Consolidated Stock Overview
        </h4>
        <div>
            <button class="btn btn-outline-success">
                <i class="mdi mdi-refresh"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="categoryFilter" class="form-select" required>
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                        <select id="subcategoryFilter" class="form-select" required>
                            <option value="">All Subcategories</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="lowStockFilter">
                            <label class="form-check-label">Show Low Stock Only (< 10)</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table id="stockOverviewTable" class="table table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>SKU / Barcode</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Warehouse Qty</th>
                        <th>Stores Total Qty</th>
                        <th>Total Qty</th>
                        <th>Cost Value ($)</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Same DataTables init as before + error handling
$(document).ready(function() {
    let table = $('#stockOverviewTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route('warehouse.stock-control.overview.data') }}',
            data: function(d) {
                d.category_id = $('#categoryFilter').val();
                d.subcategory_id = $('#subcategoryFilter').val();
                d.low_stock = $('#lowStockFilter').is(':checked') ? 1 : 0;
            },
            error: function(xhr) {
                $('#stockOverviewTable').html(
                    '<tr><td colspan="9" class="text-center py-5 text-danger">' +
                    '<i class="mdi mdi-alert-circle-outline fs-2 mb-3 d-block"></i>' +
                    '<strong>Data Load Failed</strong><br>Please try again or contact support.' +
                    '</td></tr>'
                );
            }
        },
        columns: [
            { data: 'product_name' },
            { data: 'sku', render: data => data || '-' },
            { data: 'category_name', defaultContent: '-' },
            { data: 'subcategory_name', defaultContent: '-' },
            { data: 'warehouse_qty' },
            { data: 'total_stores_qty' },
            { data: 'total_qty' },
            { data: 'value', render: data => '$ ' + parseFloat(data || 0).toLocaleString('en-IN') }
        ],
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
    });

    $('#categoryFilter').change(function() {
        let catId = $(this).val();
        $('#subcategoryFilter').html('<option value="">Loading...</option>');
        if (catId) {
            $.get('/warehouse/fetch-subcategories/' + catId, function(data) {
                let html = '<option value="">All Subcategories</option>';
                data.forEach(i => html += `<option value="${i.id}">${i.name}</option>`);
                $('#subcategoryFilter').html(html);
            });
        }
        table.draw();
    });

    $('#subcategoryFilter, #lowStockFilter').change(() => table.draw());
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

</x-app-layout>