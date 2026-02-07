<x-app-layout title="Transfer Monitor">
    <div class="container-fluid">

        @include('warehouse.partials.breadcrumb', [
            'title' => 'Inter-Store Transfer Monitor',
            'items' => [['text' => 'Dashboard', 'url' => route('dashboard')], ['text' => 'Transfers']]
        ])

        {{-- Stats Overview --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="mdi mdi-truck-fast text-primary fs-1 me-3"></i>
                        <div>
                            <h5 class="fw-bold mb-0">{{ \App\Models\StockTransfer::where('status', 'in_transit')->count() }}</h5>
                            <small class="text-muted">Currently In-Transit</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="mdi mdi-clock-outline text-warning fs-1 me-3"></i>
                        <div>
                            <h5 class="fw-bold mb-0">{{ \App\Models\StockTransfer::where('status', 'pending')->count() }}</h5>
                            <small class="text-muted">Pending Approval</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Live Transfer Log</h5>
            </div>
            <div class="card-body p-0">
                <table id="transfersTable" class="table table-hover align-middle w-100">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Transfer #</th>
                            <th>Route (From <i class="mdi mdi-arrow-right"></i> To)</th>
                            <th>Date</th>
                            <th>Total Items</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Details Modal --}}
    <div class="modal fade" id="transferModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Transfer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" id="modalContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
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
                    { data: 'transfer_no', name: 'transfer_number', className: 'ps-4' },
                    { data: 'route', name: 'fromStore.store_name' },
                    { data: 'date', name: 'created_at' },
                    { data: 'items_count', searchable: false },
                    { data: 'status', name: 'status' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end pe-4' }
                ],
                order: [[2, 'desc']]
            });

            // Load Modal
            $(document).on('click', '.view-details', function() {
                let id = $(this).data('id');
                $('#transferModal').modal('show');
                $('#modalContent').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
                
                $.get("/warehouse/transfers/monitor/" + id, function(data) {
                    $('#modalContent').html(data.html);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>