<x-app-layout title="Consolidated Stock Overview">

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Consolidated Stock Overview</h4>
            <small class="text-muted">Real-time stock across warehouse and all stores</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Category</label>
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Subcategory</label>
                    <select id="subcategoryFilter" class="form-select">
                        <option value="">All Subcategories</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" id="lowStockFilter" class="form-check-input">
                        <label class="form-check-label">Low Stock Only (< 10)</label>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="stockOverviewTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU / Barcode</th>
                            <th>Category</th>
                            <th>Subcategory</th>
                            <th>Warehouse Qty</th>
                            <th>Stores Total Qty</th>
                            <th>Total Qty</th>
                            <th>Cost Value (₹)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
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
            }
        },
        columns: [
            { data: 'product_name', name: 'product_name' },
            { data: 'sku', name: 'sku', render: data => `${data || '-'} / ${row.barcode || '-'}` },
            { data: 'category_name', name: 'category_name' },
            { data: 'subcategory_name', name: 'subcategory_name' },
            { data: 'warehouse_qty', name: 'warehouse_qty' },
            { data: 'total_stores_qty', name: 'total_stores_qty' },
            { data: 'total_qty', name: 'total_qty' },
            { data: 'value', name: 'value' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        order: [[6, 'desc']],
        lengthMenu: [10, 25, 50, 100]
    });

    $('#categoryFilter, #subcategoryFilter, #lowStockFilter').change(function() {
        table.draw();
    });

    $('#categoryFilter').change(function() {
        let catId = $(this).val();
        $('#subcategoryFilter').html('<option value="">Loading...</option>');
        if (catId) {
            $.get('/warehouse/fetch-subcategories/' + catId, function(data) {
                let options = '<option value="">All Subcategories</option>';
                data.forEach(item => options += `<option value="${item.id}">${item.name}</option>`);
                $('#subcategoryFilter').html(options);
            });
        } else {
            $('#subcategoryFilter').html('<option value="">All Subcategories</option>');
        }
    });
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

</x-app-layout>