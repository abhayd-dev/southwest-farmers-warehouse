<x-app-layout title="Discrepancies & Returns">
    <div class="container-fluid">
        
        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    {{-- Title --}}
                    <div class="d-flex flex-column gap-2">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-alert-circle-outline text-danger me-2"></i> Discrepancies & Returns
                        </h4>
                        <small class="text-muted">Track fulfillment shortages and store returns</small>
                    </div>

                    {{-- Actions / Refresh Button --}}
                    <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-md-end">
                        <button type="button" class="btn btn-outline-secondary shadow-sm flex-fill flex-md-grow-0" id="refreshBtn">
                            <i class="mdi mdi-refresh me-1"></i> Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABS & CONTENT --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom pt-3 pb-0 px-3">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#transferIssues" role="tab">
                            <i class="mdi mdi-transfer me-1"></i> Transfer Shortages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#storeReturns" role="tab">
                            <i class="mdi mdi-undo-variant me-1"></i> Store Returns
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content p-3">
                    
                    {{-- TAB 1: TRANSFER ISSUES --}}
                    <div class="tab-pane fade show active" id="transferIssues">
                        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 d-flex align-items-center mb-4">
                            <i class="mdi mdi-information-outline fs-5 me-2"></i>
                            <div>
                                Showing Completed Orders where <strong>Sent Qty</strong> is less than <strong>Requested Qty</strong>.
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="transferTable" class="table table-hover align-middle mb-0 text-nowrap w-100">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="px-3 py-3 text-uppercase small fw-bold">Request ID</th>
                                        <th class="py-3 text-uppercase small fw-bold">Store</th>
                                        <th class="py-3 text-uppercase small fw-bold">Product</th>
                                        <th class="py-3 text-uppercase small fw-bold text-center">Requested</th>
                                        <th class="py-3 text-uppercase small fw-bold text-center">Sent</th>
                                        <th class="py-3 text-uppercase small fw-bold text-center text-danger">Shortage</th>
                                        <th class="py-3 text-uppercase small fw-bold">Date</th>
                                        <th class="px-3 py-3 text-uppercase small fw-bold text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 2: STORE RETURNS --}}
                    <div class="tab-pane fade" id="storeReturns">
                        <div class="table-responsive">
                            <table id="returnsTable" class="table table-hover align-middle mb-0 text-nowrap w-100">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="px-3 py-3 text-uppercase small fw-bold">Return ID</th>
                                        <th class="py-3 text-uppercase small fw-bold">Store</th>
                                        <th class="py-3 text-uppercase small fw-bold">Product</th>
                                        <th class="py-3 text-uppercase small fw-bold text-center">Req. Qty</th>
                                        <th class="py-3 text-uppercase small fw-bold text-center">Status</th>
                                        <th class="py-3 text-uppercase small fw-bold">Reason</th>
                                        <th class="px-3 py-3 text-uppercase small fw-bold text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Transfer Issues Table
            let transferTable = $('#transferTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.discrepancy.transfer-issues') }}",
                columns: [
                    { data: 'request_id', name: 'id', className: 'px-3 fw-bold' },
                    { data: 'store_name', name: 'store.store_name' },
                    { data: 'product_name', name: 'product.product_name' },
                    { data: 'received_quantity', className: 'text-center', name: 'requested_quantity' }, 
                    { data: 'dispatched_quantity', className: 'text-center', name: 'fulfilled_quantity' }, 
                    { data: 'discrepancy', className: 'text-center fw-bold text-danger', orderable: false, searchable: false },
                    { data: 'date', name: 'updated_at' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end px-3' }
                ],
                order: [[6, 'desc']],
                language: {
                    search: "",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "Show _MENU_",
                },
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3"f<"d-flex align-items-center"l>>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-3"ip>'
            });

            // Store Returns Table
            let returnsTable = $('#returnsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.discrepancy.store-returns') }}",
                columns: [
                    { data: 'recall_id', name: 'id', className: 'px-3 fw-bold' },
                    { data: 'store_name', name: 'store.store_name' },
                    { data: 'product_name', name: 'product.product_name' },
                    { data: 'requested_quantity', className: 'text-center', name: 'quantity' },
                    { data: 'status_badge', className: 'text-center', name: 'status' },
                    { data: 'reason', name: 'reason' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end px-3' }
                ],
                order: [[0, 'desc']],
                language: {
                    search: "",
                    searchPlaceholder: "Search returns...",
                    lengthMenu: "Show _MENU_",
                },
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center mb-3"f<"d-flex align-items-center"l>>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center mt-3"ip>'
            });

            // Refresh Button Logic
            $('#refreshBtn').on('click', function() {
                const btn = $(this);
                const icon = btn.find('i');
                
                // Add spin animation
                icon.addClass('mdi-spin');
                
                // Reload tables
                transferTable.ajax.reload(null, false);
                returnsTable.ajax.reload(null, false);
                
                // Remove spin after 1s
                setTimeout(() => {
                    icon.removeClass('mdi-spin');
                }, 1000);
            });
        });
    </script>
    @endpush
</x-app-layout>