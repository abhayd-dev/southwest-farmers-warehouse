<x-app-layout title="Transfer Monitor">
    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.partials.breadcrumb', [
                            'title' => 'Inter-Store Transfer Monitor',
                            'items' => [['text' => 'Dashboard', 'url' => route('dashboard')], ['text' => 'Transfers']]
                        ])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-monitor-dashboard text-primary me-2"></i> Transfer Monitor
                        </h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary shadow-sm" onclick="window.location.reload()">
                            <i class="mdi mdi-refresh me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS OVERVIEW --}}
        <div class="row g-3 mb-4">
            {{-- In-Transit Card --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-truck-fast text-primary fs-4"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">{{ \App\Models\StockTransfer::where('status', 'in_transit')->count() }}</h4>
                                <small class="text-muted fw-semibold">Currently In-Transit</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Approval Card --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-clock-outline text-warning fs-4"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">{{ \App\Models\StockTransfer::where('status', 'pending')->count() }}</h4>
                                <small class="text-muted fw-semibold">Pending Approval</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Completed Card (Optional Visual Balance) --}}
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-check-circle-outline text-success fs-4"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">{{ \App\Models\StockTransfer::where('status', 'completed')->count() }}</h4>
                                <small class="text-muted fw-semibold">Completed Transfers</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
             {{-- Rejected Card (Optional Visual Balance) --}}
             <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-close-circle-outline text-danger fs-4"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">{{ \App\Models\StockTransfer::where('status', 'rejected')->count() }}</h4>
                                <small class="text-muted fw-semibold">Rejected / Cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="mdi mdi-clipboard-list-outline me-1"></i> Live Transfer Log
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="transfersTable" class="table table-hover align-middle mb-0 text-nowrap w-100">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase small fw-bold">Transfer #</th>
                                <th class="py-3 text-uppercase small fw-bold">Route (From <i class="mdi mdi-arrow-right mx-1"></i> To)</th>
                                <th class="py-3 text-uppercase small fw-bold">Date</th>
                                <th class="py-3 text-uppercase small fw-bold text-center">Total Items</th>
                                <th class="py-3 text-uppercase small fw-bold text-center">Status</th>
                                <th class="text-end pe-4 py-3 text-uppercase small fw-bold">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- DETAILS MODAL --}}
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="mdi mdi-file-document-outline me-2"></i> Transfer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="modalContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 small">Loading transfer details...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#transfersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.transfers.monitor') }}",
                columns: [
                    { data: 'transfer_no', name: 'transfer_number', className: 'ps-4 fw-bold text-dark' },
                    { data: 'route', name: 'fromStore.store_name' },
                    { data: 'date', name: 'created_at', className: 'text-muted' },
                    { data: 'items_count', searchable: false, className: 'text-center fw-semibold' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end pe-4' }
                ],
                order: [[2, 'desc']], // Sort by Date DESC
                language: {
                    search: "",
                    searchPlaceholder: "Search transfer #...",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                },
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"f<"d-flex align-items-center gap-2"l>>rt<"d-flex flex-column flex-md-row justify-content-between align-items-center p-3 gap-2"ip>'
            });

            // Load Modal
            $(document).on('click', '.view-details', function() {
                let id = $(this).data('id');
                $('#transferModal').modal('show');
                
                // Reset Modal Content
                $('#modalContent').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small">Fetching details...</p>
                    </div>
                `);
                
                $.get("/warehouse/transfers/monitor/" + id, function(data) {
                    $('#modalContent').html(data.html);
                }).fail(function() {
                    $('#modalContent').html(`
                        <div class="text-center py-5 text-danger">
                            <i class="mdi mdi-alert-circle-outline fs-1"></i>
                            <p class="mt-2">Failed to load details. Please try again.</p>
                        </div>
                    `);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>