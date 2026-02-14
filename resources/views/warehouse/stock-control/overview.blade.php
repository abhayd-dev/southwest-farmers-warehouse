<x-app-layout title="Consolidated Stock Overview">

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="bg-white border-bottom shadow-sm mb-4">
        <div class="py-3">
            <div class="d-flex flex-column gap-2">
                @include('warehouse.partials.breadcrumb', ['title' => 'Consolidated Stock Overview'])
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-view-dashboard-outline text-primary me-2"></i> Consolidated Stock Overview
                </h4>
            </div>
        </div>
    </div>

    {{-- FILTERS CARD --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form class="needs-validation" novalidate>
                <div class="row g-3">
                    {{-- DEPARTMENT FILTER --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Department</label>
                        <select id="departmentFilter" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- CATEGORY FILTER --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Category</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- SUBCATEGORY FILTER --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Subcategory</label>
                        <select id="subcategoryFilter" class="form-select">
                            <option value="">All Subcategories</option>
                        </select>
                    </div>
                    
                    {{-- REFRESH BUTTON --}}
                    <div class="col-12 col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-success w-100" onclick="window.location.reload()">
                            <i class="mdi mdi-refresh me-1"></i> Refresh Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="stockOverviewTable" class="table table-hover table-bordered align-middle mb-0 text-nowrap w-100">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="px-3 py-3 text-uppercase small fw-bold">Product</th>
                            <th class="py-3 text-uppercase small fw-bold">SKU</th>
                            <th class="py-3 text-uppercase small fw-bold">Department</th>
                            <th class="py-3 text-uppercase small fw-bold">Category</th>
                            <th class="py-3 text-uppercase small fw-bold">Subcategory</th>
                            <th class="py-3 text-uppercase small fw-bold text-center">Warehouse Qty</th>
                            <th class="py-3 text-uppercase small fw-bold text-center">Stores Qty</th>
                            <th class="py-3 text-uppercase small fw-bold text-center">Total Qty</th>
                            <th class="px-3 py-3 text-uppercase small fw-bold text-end">Cost Value</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#stockOverviewTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route('warehouse.stock-control.overview.data') }}',
            data: function(d) {
                d.department_id = $('#departmentFilter').val();
                d.category_id = $('#categoryFilter').val();
                d.subcategory_id = $('#subcategoryFilter').val();
                // Removed low_stock parameter
            },
            error: function(xhr) {
                $('#stockOverviewTable tbody').html(
                    '<tr><td colspan="9" class="text-center py-5 text-danger">' +
                    '<i class="mdi mdi-alert-circle-outline fs-2 mb-3 d-block"></i>' +
                    '<strong>Data Load Failed</strong><br>Please try again or contact support.' +
                    '</td></tr>'
                );
            }
        },
        columns: [
            { data: 'product_name', className: 'px-3 fw-semibold text-dark' },
            { data: 'sku', render: data => data || '-', className: 'font-monospace' },
            { data: 'department_name', defaultContent: '-' },
            { data: 'category_name', defaultContent: '-' },
            { data: 'subcategory_name', defaultContent: '-' },
            { data: 'warehouse_qty', className: 'text-center fw-bold text-primary' },
            { data: 'total_stores_qty', className: 'text-center fw-bold text-info' },
            { data: 'total_qty', className: 'text-center fw-bold text-success' },
            { data: 'value', className: 'text-end px-3 fw-bold', render: data => '$ ' + parseFloat(data || 0).toLocaleString('en-US', {minimumFractionDigits: 2}) }
        ],
        dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"Bf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"ip>',
        buttons: [
            { extend: 'copy', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'csv', className: 'btn btn-sm btn-outline-secondary' },
            { extend: 'excel', className: 'btn btn-sm btn-outline-success' },
            { extend: 'pdf', className: 'btn btn-sm btn-outline-danger' },
            { extend: 'print', className: 'btn btn-sm btn-outline-info' }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search products...",
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });

    // Dynamic Subcategory Loading
    $('#categoryFilter').change(function() {
        let catId = $(this).val();
        let subSelect = $('#subcategoryFilter');
        
        subSelect.html('<option value="">Loading...</option>');
        
        if (catId) {
            $.get('/warehouse/fetch-subcategories/' + catId, function(data) {
                let html = '<option value="">All Subcategories</option>';
                if(Array.isArray(data)) {
                    data.forEach(i => html += `<option value="${i.id}">${i.name}</option>`);
                }
                subSelect.html(html);
            }).fail(function() {
                subSelect.html('<option value="">Error loading</option>');
            });
        } else {
            subSelect.html('<option value="">All Subcategories</option>');
        }
        table.draw();
    });

    // Redraw table on filter change
    $('#departmentFilter, #subcategoryFilter').change(() => table.draw());
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<style>
    /* Custom spacing for DataTable buttons */
    div.dt-buttons {
        gap: 0.5rem;
        display: flex;
        flex-wrap: wrap;
    }
</style>
@endpush

</x-app-layout>