<x-app-layout title="Inventory Count">
    <div class="container-fluid">
        
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Inventory Count',
            'items' => [
                ['text' => 'Dashboard', 'url' => route('dashboard')],
                ['text' => 'Stock Control', 'url' => route('warehouse.stock-control.overview')],
                ['text' => 'Inventory Count']
            ]
        ])

        {{-- START BUTTON (Protected) --}}
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_audits'))
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('warehouse.stock-control.audit.create') }}" class="btn btn-primary shadow-sm">
                <i class="mdi mdi-plus-circle me-1"></i> Start New Audit
            </a>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <form id="auditFilterForm" class="row align-items-center g-2">
                    <div class="col-md-auto">
                        <label class="form-label fw-bold mb-0 small text-muted">Date Range:</label>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" placeholder="Start Date">
                    </div>
                    <div class="col-md-auto text-center px-1">
                        <span class="text-muted small">to</span>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" placeholder="End Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="mdi mdi-filter"></i> Filter</button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table id="auditsTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th>Audit #</th>
                            <th>Scope / Department</th> 
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
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#auditsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('warehouse.stock-control.audit.index') }}",
                    data: function(d) {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [
                    { data: 'audit_no', name: 'audit_number', className: 'fw-bold text-primary' },
                    { data: 'type_label', name: 'type' }, 
                    { data: 'status_badge', name: 'status' },
                    { data: 'initiator_name', name: 'initiator.name', defaultContent: 'System' },
                    { data: 'date', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[4, 'desc']]
            });

            $('#auditFilterForm').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });
        });
    </script>
    @endpush
</x-app-layout>