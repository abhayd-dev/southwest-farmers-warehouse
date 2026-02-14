<x-app-layout title="Transaction Ledger">
    <div class="container-fluid">
        
        {{-- HEADER WITH EXPORT --}}
        <div class="bg-white border-bottom p-4 mb-4 shadow-sm">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-book-open-page-variant text-primary me-2"></i> Transaction Ledger
                    </h4>
                    <p class="text-muted mb-0 small">Complete history of Inbound, Outbound, and Internal movements</p>
                </div>
                <button class="btn btn-success shadow-sm px-4 w-40 justify-content-end" id="exportBtn">
                    <i class="mdi mdi-file-excel me-1"></i> Export CSV
                </button>
            </div>
        </div>

        {{-- FILTERS CARD --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-filter-variant me-1"></i> Filter Transactions</h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- Start Date --}}
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold text-muted small text-uppercase">Start Date</label>
                        <input type="date" id="filter_start" class="form-control shadow-sm">
                    </div>

                    {{-- End Date --}}
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label fw-bold text-muted small text-uppercase">End Date</label>
                        <input type="date" id="filter_end" class="form-control shadow-sm">
                    </div>

                    {{-- Movement Type --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Movement Type</label>
                        <select id="filter_type" class="form-select shadow-sm">
                            <option value="">All Movements</option>
                            <option value="purchase_in">Purchase In (Inbound)</option>
                            <option value="dispatch">Dispatch (Outbound)</option>
                            <option value="recall_in">Recall/Return (Inbound)</option>
                            <option value="adjustment">Adjustments</option>
                        </select>
                    </div>

                    {{-- Product Search --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Search Product</label>
                        <select id="filter_product" class="form-select shadow-sm">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions (Apply & Reset) --}}
                    <div class="col-12 col-lg-2 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button class="btn btn-primary flex-grow-1 shadow-sm" id="applyFilters">
                                <i class="mdi mdi-filter-check me-1"></i> Apply
                            </button>
                            <button class="btn btn-outline-secondary shadow-sm" id="resetFilters" title="Reset Filters">
                                <i class="mdi mdi-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEDGER TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="ledgerTable" class="table table-hover align-middle w-100 mb-0 text-nowrap">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="py-3 ps-4 text-uppercase small fw-bold">Date</th>
                                <th class="py-3 text-uppercase small fw-bold">Type</th>
                                <th class="py-3 text-uppercase small fw-bold">Product Details</th>
                                <th class="py-3 text-end text-uppercase small fw-bold">Qty Change</th>
                                <th class="py-3 text-end text-uppercase small fw-bold">Balance</th>
                                <th class="py-3 text-uppercase small fw-bold">Reference</th>
                                <th class="py-3 pe-4 text-uppercase small fw-bold">User</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            let table = $('#ledgerTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('warehouse.finance.ledger') }}",
                    data: function(d) {
                        d.start_date = $('#filter_start').val();
                        d.end_date = $('#filter_end').val();
                        d.type = $('#filter_type').val();
                        d.product_id = $('#filter_product').val();
                    }
                },
                columns: [
                    { data: 'date', name: 'created_at', className: 'ps-4 text-muted' },
                    { data: 'type_badge', name: 'type' },
                    { data: 'product_details', name: 'product.product_name', className: 'fw-semibold text-dark' },
                    { data: 'quantity', name: 'quantity_change', className: 'text-end fw-bold' },
                    { data: 'balance', name: 'running_balance', className: 'text-end text-muted' },
                    { data: 'reference', name: 'reference_id', className: 'font-monospace small' },
                    { data: 'user', name: 'user.name', className: 'pe-4 small' }
                ],
                order: [[0, 'desc']],
                pageLength: 20,
                language: {
                    search: "",
                    searchPlaceholder: "Search records...",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                },
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"l>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"ip>'
            });

            // Apply Filters
            $('#applyFilters').click(function() {
                table.draw();
            });

            // Reset Filters
            $('#resetFilters').click(function() {
                $('#filter_start').val('');
                $('#filter_end').val('');
                $('#filter_type').val('');
                $('#filter_product').val('');
                table.draw();
            });

            // Export Logic
            $('#exportBtn').click(function() {
                let params = $.param({
                    start_date: $('#filter_start').val(),
                    end_date: $('#filter_end').val(),
                    type: $('#filter_type').val(),
                    product_id: $('#filter_product').val()
                });
                window.location.href = "{{ route('warehouse.finance.ledger.export') }}?" + params;
            });
        });
    </script>
    @endpush
</x-app-layout>