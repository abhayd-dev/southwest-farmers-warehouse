<x-app-layout title="Stock Audits">
    <div class="container-fluid">
        
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Cycle Counts / Audits',
            'items' => [
                ['text' => 'Dashboard', 'url' => route('dashboard')],
                ['text' => 'Stock Control', 'url' => route('warehouse.stock-control.overview')],
                ['text' => 'Stock Audits']
            ]
        ])

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('warehouse.stock-control.audit.create') }}" class="btn btn-primary shadow-sm">
                <i class="mdi mdi-plus-circle me-1"></i> Start New Audit
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table id="auditsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Audit #</th>
                            <th>Scope / Department</th> {{-- Renamed Column --}}
                            <th>Status</th>
                            <th>Initiated By</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#auditsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('warehouse.stock-control.audit.index') }}",
                columns: [
                    { data: 'audit_no', name: 'audit_number', className: 'fw-bold text-primary' },
                    { data: 'type_label', name: 'type' }, // Uses the HTML column from Controller
                    { data: 'status_badge', name: 'status' },
                    { data: 'initiator.name', name: 'initiator.name', defaultContent: 'System' },
                    { data: 'date', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[4, 'desc']]
            });
        });
    </script>
    @endpush
</x-app-layout>