<x-app-layout title="Expiry & Aging Report">
    <div class="container-fluid">
        
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0 bg-white p-3 rounded shadow-sm">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-dark">Dashboard</a></li>
                <li class="breadcrumb-item text-muted">Reports</li>
                <li class="breadcrumb-item active fw-bold">Expiry Risk Analysis (FEFO)</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="mdi mdi-calendar-clock text-danger me-2"></i> Expiry & Aging Report</h4>
                <small class="text-muted">Track batches expiring soon to minimize loss</small>
            </div>
            <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                <i class="mdi mdi-printer me-1"></i> Print Report
            </button>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Risk Level</label>
                        <select id="statusFilter" class="form-select shadow-none">
                            <option value="">All Batches</option>
                            <option value="expired" class="text-danger fw-bold">Already Expired</option>
                            <option value="critical" class="text-danger">Critical (< 30 Days)</option>
                            <option value="warning" class="text-warning">Warning (30 - 90 Days)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Department</label>
                        <select id="deptFilter" class="form-select shadow-none">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Category</label>
                        <select id="catFilter" class="form-select shadow-none">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100 shadow-sm" id="applyFilters">
                            <i class="mdi mdi-filter me-1"></i> Filter Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                <table id="expiryTable" class="table table-hover align-middle w-100 mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Product Details</th>
                            <th>Department</th>
                            <th>Batch No.</th>
                            <th class="text-center">Current Stock</th>
                            <th>Expiry Date</th>
                            <th class="text-center">Status / Days Left</th>
                            <th class="text-end pe-4">Stock Value</th>
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
            let table = $('#expiryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('warehouse.reports.expiry') }}",
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                        d.department_id = $('#deptFilter').val();
                        d.category_id = $('#catFilter').val();
                    }
                },
                columns: [
                    { data: 'product_name', name: 'product.product_name', className: 'ps-4' },
                    { data: 'department', name: 'product.department.name' },
                    { data: 'batch_info', name: 'batch_number' },
                    { data: 'quantity', name: 'quantity', className: 'text-center fw-bold' },
                    { data: 'expiry_date', name: 'expiry_date' },
                    { data: 'days_left', searchable: false, className: 'text-center' },
                    { data: 'stock_value', searchable: false, className: 'text-end pe-4' }
                ],
                order: [[4, 'asc']], // Sort by Expiry Date (Ascending - nearest first)
                pageLength: 25
            });

            $('#applyFilters').click(function() {
                table.draw();
            });
        });
    </script>
    @endpush
</x-app-layout>