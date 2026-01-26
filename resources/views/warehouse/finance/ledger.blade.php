<x-app-layout title="Transaction Ledger">
    <div class="container-fluid">
        
        {{-- HEADER WITH FILTERS --}}
        <div class="bg-white border-bottom p-4 mb-4 shadow-sm rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-book-open-page-variant text-primary me-2"></i> Transaction Ledger
                    </h4>
                    <p class="text-muted mb-0 small">Complete history of Inbound, Outbound, and Internal movements</p>
                </div>
                <button class="btn btn-success shadow-sm px-4" id="exportBtn">
                    <i class="mdi mdi-file-excel me-1"></i> Export CSV
                </button>
            </div>

            {{-- PROFESSIONAL FILTER BAR --}}
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="small fw-bold text-muted mb-1 text-uppercase">Start Date</label>
                            <input type="date" id="filter_start" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="small fw-bold text-muted mb-1 text-uppercase">End Date</label>
                            <input type="date" id="filter_end" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted mb-1 text-uppercase">Movement Type</label>
                            <select id="filter_type" class="form-select form-select-sm border-0 shadow-sm">
                                <option value="">All Movements</option>
                                <option value="purchase_in">Purchase In (Inbound)</option>
                                <option value="dispatch">Dispatch (Outbound)</option>
                                <option value="recall_in">Recall/Return (Inbound)</option>
                                <option value="adjustment">Adjustments</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-bold text-muted mb-1 text-uppercase">Search Product</label>
                            <select id="filter_product" class="form-select form-select-sm border-0 shadow-sm">
                                <option value="">All Products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-primary w-100 shadow-sm py-2" id="applyFilters">
                                <i class="mdi mdi-filter-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEDGER TABLE --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="ledgerTable" class="table table-hover align-middle w-100" style="margin: 0 !important;">
                        <thead class="bg-light text-uppercase text-muted small">
                            <tr>
                                <th class="py-3 ps-3">Date</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Product Details</th>
                                <th class="py-3 text-end">Qty Change</th>
                                <th class="py-3 text-end">Balance</th>
                                <th class="py-3">Reference</th>
                                <th class="py-3">User</th>
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
                    { data: 'date', name: 'created_at', className: 'ps-3' },
                    { data: 'type_badge', name: 'type' },
                    { data: 'product_details', name: 'product.product_name' },
                    { data: 'quantity', name: 'quantity_change', className: 'text-end' },
                    { data: 'balance', name: 'running_balance', className: 'text-end' },
                    { data: 'reference', name: 'reference_id' },
                    { data: 'user', name: 'user.name' }
                ],
                order: [[0, 'desc']],
                pageLength: 20,
                dom: 'tr<"d-flex justify-content-between p-3"ip>' // Custom layout to hide search/length
            });

            // Filter Button Action
            $('#applyFilters').click(function() {
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