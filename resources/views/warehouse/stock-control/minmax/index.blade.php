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

            {{-- ADD BUTTON (Protected) --}}
            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_min_max'))
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#minMaxModal" id="addNewBtn">
                    <i class="mdi mdi-plus-circle me-1"></i> Add New Level
                </button>
            @endif
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="minMaxTable" class="table table-hover table-bordered align-middle mb-0"
                        style="width:100%">
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

        {{-- MODAL (Protected Content) --}}
        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_min_max'))
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
                                        <input type="number" name="min_level" id="minLevel" class="form-control"
                                            min="0" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Max Level</label>
                                        <input type="number" name="max_level" id="maxLevel" class="form-control"
                                            min="1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Reorder Qty</label>
                                        <input type="number" name="reorder_quantity" id="reorderQty"
                                            class="form-control" min="1" required>
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
        @endif

    </div>

    @push('scripts')
        <script>
            $(function() {
                // Permission Flag for JS
                const canManage =
                    {{ auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_min_max') ? 'true' : 'false' }};

                let table = $('#minMaxTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('warehouse.stock-control.minmax.data') }}',
                    columns: [{
                            data: 'product_name'
                        },
                        {
                            data: 'sku'
                        },
                        {
                            data: 'category_name'
                        },
                        {
                            data: 'min_level'
                        },
                        {
                            data: 'max_level'
                        },
                        {
                            data: 'reorder_qty'
                        },
                        {
                            data: 'current_qty'
                        },
                        {
                            data: 'status'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false,
                            visible: canManage // Hide column if no permission
                        }
                    ]
                });

                // ... (Rest of JS for Modal/Edit Logic remains same, inside if check) ...
                if (canManage) {
                    // Open Modal for NEW Entry
                    $('#addNewBtn').click(() => {
                        $('#minMaxModalLabel').text('Add Min-Max Level');
                        $('#minmaxId').val('');
                        $('#minMaxForm')[0].reset();
                        $('#productSelect').prop('disabled', false).val('').trigger('change');
                        $('#minLevel').val(5);
                        $('#maxLevel').val(100);
                        $('#reorderQty').val(20);
                        $('#saveBtn').text('Save');
                    });

                    // Open Modal for EDIT Entry
                    $('#minMaxTable').on('click', '.edit-minmax', function() {
                        $('#minMaxModalLabel').text('Edit Min-Max Level');
                        let productId = $(this).data('id');
                        let min = $(this).data('min');
                        let max = $(this).data('max');
                        let reorder = $(this).data('reorder');

                        $('#minmaxId').val(productId);
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
                                    text: res.message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: err => {
                                Swal.fire('Error', err.responseJSON?.message ?? 'Failed to save',
                                    'error');
                            }
                        });
                    });
                }
            });
        </script>
    @endpush

</x-app-layout>
