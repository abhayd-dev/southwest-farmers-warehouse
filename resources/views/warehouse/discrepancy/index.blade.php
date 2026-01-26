<x-app-layout title="Discrepancies & Returns">
    <div class="container-fluid">
        
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-alert-circle-outline text-danger me-2"></i> Discrepancies & Returns
                </h4>
                <small class="text-muted">Track transfer shortages and store returns</small>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#transferIssues" role="tab">
                            <i class="mdi mdi-transfer me-1"></i> Transfer Issues (Shortages)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#storeReturns" role="tab">
                            <i class="mdi mdi-undo-variant me-1"></i> Store Returns
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">
                    
                    {{-- TAB 1: TRANSFER ISSUES --}}
                    <div class="tab-pane fade show active" id="transferIssues">
                        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 mb-3">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Showing Completed Orders where <strong>Received Qty</strong> is less than <strong>Dispatched Qty</strong>.
                        </div>
                        
                        <div class="table-responsive">
                            <table id="transferTable" class="table table-hover table-bordered w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Store</th>
                                        <th>Product</th>
                                        <th class="text-center">Sent</th>
                                        <th class="text-center">Received</th>
                                        <th class="text-center">Shortage</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 2: STORE RETURNS --}}
                    <div class="tab-pane fade" id="storeReturns">
                        <div class="table-responsive">
                            <table id="returnsTable" class="table table-hover table-bordered w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Return ID</th>
                                        <th>Store</th>
                                        <th>Product</th>
                                        <th class="text-center">Req. Qty</th>
                                        <th class="text-center">Status</th>
                                        <th>Reason</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
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
            $('#transferTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.discrepancy.transfer-issues') }}",
                columns: [
                    { data: 'request_id' },
                    { data: 'store_name' },
                    { data: 'product_name' },
                    { data: 'dispatched_quantity', className: 'text-center' },
                    { data: 'received_quantity', className: 'text-center' },
                    { data: 'discrepancy', className: 'text-center' },
                    { data: 'date' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' }
                ]
            });

            // Store Returns Table
            $('#returnsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.discrepancy.store-returns') }}",
                columns: [
                    { data: 'recall_id' },
                    { data: 'store_name' },
                    { data: 'product_name' },
                    { data: 'requested_quantity', className: 'text-center' },
                    { data: 'status_badge', className: 'text-center' },
                    { data: 'reason' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' }
                ]
            });
        });
    </script>
    @endpush
</x-app-layout>