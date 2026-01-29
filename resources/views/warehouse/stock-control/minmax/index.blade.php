<x-app-layout title="Min-Max Stock Levels">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-sort-variant-lock text-primary me-2"></i>
                    Min-Max Stock Levels
                </h4>
                <small class="text-muted">Define reorder points and safety stock per product</small>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#minMaxModal" id="addNewBtn">
                <i class="mdi mdi-plus-circle me-1"></i> Add New Level
            </button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="minMaxTable" class="table table-hover table-bordered align-middle mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Min Level</th>
                                <th>Max Level</th>
                                <th>Reorder Qty</th>
                                <th>Current Qty</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="minMaxModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="minMaxModalLabel">Set Min-Max Levels</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="minMaxForm" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" id="minmaxId" name="id">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select name="product_id" id="productSelect" class="form-select" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->product_name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a product</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Min Level</label>
                                    <input type="number" name="min_level" id="minLevel" class="form-control" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Max Level</label>
                                    <input type="number" name="max_level" id="maxLevel" class="form-control" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reorder Qty</label>
                                    <input type="number" name="reorder_quantity" id="reorderQty" class="form-control" min="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="mdi mdi-content-save"></i> Save Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            $(function() {

                let table = $('#minMaxTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('warehouse.stock-control.minmax.data') }}',
                    columns: [
                        { data: 'product_name' },
                        { data: 'sku' },
                        { data: 'category_name' },
                        { data: 'min_level' },
                        { data: 'max_level' },
                        { data: 'reorder_qty' },
                        { data: 'current_qty' },
                        { data: 'status' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                });

                // Open Modal for NEW Entry
                $('#addNewBtn').click(() => {
                    $('#minMaxModalLabel').text('Add Min-Max Level');
                    $('#minmaxId').val(''); // Clear ID
                    $('#minMaxForm')[0].reset();
                    
                    // ENABLE product select for new entries
                    $('#productSelect').prop('disabled', false).val('').trigger('change');
                    
                    $('#minLevel').val(5);
                    $('#maxLevel').val(100);
                    $('#reorderQty').val(20);
                    $('#saveBtn').text('Save');
                });

                // Open Modal for EDIT Entry
                $('#minMaxTable').on('click', '.edit-minmax', function() {
                    $('#minMaxModalLabel').text('Edit Min-Max Level');
                    
                    // Get data attributes from the button
                    let productId = $(this).data('id');
                    let min = $(this).data('min');
                    let max = $(this).data('max');
                    let reorder = $(this).data('reorder');

                    $('#minmaxId').val(productId);
                    
                    // DISABLE product select for edits (User shouldn't change product ID on edit)
                    $('#productSelect').val(productId).prop('disabled', true).trigger('change');
                    
                    $('#minLevel').val(min);
                    $('#maxLevel').val(max);
                    $('#reorderQty').val(reorder);

                    $('#saveBtn').text('Update');
                    $('#minMaxModal').modal('show');
                });

                // Submit Form
                $('#minMaxForm').submit(function(e) {
                    e.preventDefault();

                    if (!this.checkValidity()) {
                        $(this).addClass('was-validated');
                        return;
                    }

                    let id = $('#minmaxId').val();
                    
                    // Determine route and method
                    let url = id ? 
                        '{{ route('warehouse.stock-control.minmax.update', ':id') }}'.replace(':id', id) : 
                        '{{ route('warehouse.stock-control.minmax.store') }}';
                    
                    let method = id ? 'PUT' : 'POST';

                    $.ajax({
                        url: url,
                        method: method,
                        data: $(this).serialize(),
                        success: res => {
                            $('#minMaxModal').modal('hide');
                            table.ajax.reload();
                            Swal.fire({
                                title: 'Success',
                                text: res.message ?? 'Saved Successfully',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: err => {
                            Swal.fire('Error', err.responseJSON?.message ?? 'Failed to save', 'error');
                        }
                    });
                });

            });
        </script>
    @endpush

</x-app-layout>