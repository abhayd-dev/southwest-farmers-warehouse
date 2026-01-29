<x-app-layout title="Discrepancies & Returns">
    <div class="container-fluid">
        
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-alert-circle-outline text-danger me-2"></i> Discrepancies & Returns
                </h4>
                <small class="text-muted">Track fulfillment shortages and store returns</small>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#transferIssues" role="tab">
                            <i class="mdi mdi-transfer me-1"></i> Transfer Shortages
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
                            Showing Completed Orders where <strong>Sent Qty</strong> is less than <strong>Requested Qty</strong>.
                        </div>
                        
                        <div class="table-responsive">
                            <table id="transferTable" class="table table-hover table-bordered w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Store</th>
                                        <th>Product</th>
                                        <th class="text-center">Requested</th> {{-- Mapped to requested_quantity --}}
                                        <th class="text-center">Sent</th>      {{-- Mapped to fulfilled_quantity --}}
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
                    { data: 'request_id', name: 'id' },
                    { data: 'store_name', name: 'store.store_name' },
                    { data: 'product_name', name: 'product.product_name' },
                    { data: 'received_quantity', className: 'text-center', name: 'requested_quantity' }, // Mapped to Requested
                    { data: 'dispatched_quantity', className: 'text-center', name: 'fulfilled_quantity' }, // Mapped to Fulfilled (Sent)
                    { data: 'discrepancy', className: 'text-center', orderable: false, searchable: false },
                    { data: 'date', name: 'updated_at' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[6, 'desc']] // Order by Date DESC
            });

            // Store Returns Table
            $('#returnsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.discrepancy.store-returns') }}",
                columns: [
                    { data: 'recall_id', name: 'id' },
                    { data: 'store_name', name: 'store.store_name' },
                    { data: 'product_name', name: 'product.product_name' },
                    { data: 'requested_quantity', className: 'text-center', name: 'quantity' },
                    { data: 'status_badge', className: 'text-center', name: 'status' },
                    { data: 'reason', name: 'reason' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[0, 'desc']] // Order by ID DESC
            });
        });
    </script>
    @endpush
</x-app-layout>