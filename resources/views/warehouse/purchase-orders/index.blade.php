<x-app-layout title="Purchase Orders">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark"><i class="mdi mdi-cart-arrow-down text-primary"></i> Purchase Orders</h4>
                        <small class="text-muted">Manage procurement and receiving</small>
                    </div>
                    <a href="{{ route('warehouse.purchase-orders.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Create PO
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="po-table" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4">PO Number</th>
                                <th>Vendor</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Receiving Status</th>
                                <th>Status</th>
                                <th class="text-end px-4">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#po-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.purchase-orders.index') }}",
                columns: [
                    {data: 'po_number', name: 'po_number', className: 'fw-bold px-4'},
                    {data: 'vendor_name', name: 'vendor.name'},
                    {data: 'order_date', name: 'order_date'},
                    {data: 'total_amount', name: 'total_amount'},
                    {data: 'progress', name: 'progress', orderable: false, searchable: false},
                    {data: 'status_badge', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end px-4'}
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
    @endpush
</x-app-layout>