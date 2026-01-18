<x-app-layout title="Store Details - {{ $store->store_name }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stores.index') }}">Stores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $store->store_code }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">{{ $store->store_name }} <span class="badge bg-success fs-6 align-middle ms-2">Active</span></h4>
                <p class="text-muted mb-0"><i class="mdi mdi-map-marker me-1"></i> {{ $store->address }}, {{ $store->city }}</p>
            </div>
            <div>
                <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-primary shadow-sm">
                    <i class="mdi mdi-pencil me-1"></i> Edit Details
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form id="filterForm" class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-2">Date Range</label>
                        <input type="text" class="form-control form-control-sm" id="dateRange" placeholder="Select Dates">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-2">Category</label>
                        <select class="form-select form-select-sm">
                            <option selected>All Categories</option>
                            @foreach($analytics['categories'] as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-2">Region</label>
                        <select class="form-select form-select-sm" disabled>
                            <option selected>{{ $store->city }}</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-4 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary"><i class="mdi mdi-filter me-1"></i> Apply</button>
                        <button type="reset" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-refresh me-1"></i> Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Inventory Value</h6>
                        <h3 class="mb-0 fw-bold">${{ number_format($analytics['inventory_value']) }}</h3>
                        <small class="text-success"><i class="mdi mdi-trending-up me-1"></i>Current Stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Total Products</h6>
                        <h3 class="mb-0 fw-bold">{{ $analytics['inventory_items'] }}</h3>
                        <small class="text-muted">Unique SKUs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Low Stock</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $analytics['low_stock_count'] }}</h3>
                        <small class="text-danger fw-bold">Action Needed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Total Staff</h6>
                        <h3 class="mb-0 fw-bold">{{ $analytics['staff_count'] }}</h3>
                        <small class="text-muted">Active Users</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0">Weekly Sales Overview</h5>
                    </div>
                    <div class="card-body p-4">
                        <div id="salesChart" style="min-height: 300px;"></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="mdi mdi-account-group me-2 text-primary"></i>Store Staff & Users</h5>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="mdi mdi-plus me-1"></i> Add New Staff
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-4">Name</th>
                                        <th>Role</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staffMembers as $staff)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold">
                                                    {{ substr($staff->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $staff->name }}</h6>
                                                    <small class="text-muted">{{ $staff->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                                {{ $staff->role ? $staff->role->name : 'No Role' }}
                                            </span>
                                        </td>
                                        <td>{{ $staff->phone ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @if(!$staff->isStoreAdmin())
                                            <form action="{{ route('warehouse.stores.staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('Remove user?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="mdi mdi-trash-can"></i></button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No staff found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0">Category Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div id="categoryChart" style="min-height: 250px;"></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0">Location</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><span class="fw-bold">Address:</span> {{ $store->address }}</p>
                        <p class="mb-1"><span class="fw-bold">City:</span> {{ $store->city }} - {{ $store->pincode }}</p>
                        @if($store->latitude)
                            <div class="mt-3">
                                <a href="https://www.google.com/maps?q={{ $store->latitude }},{{ $store->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="mdi mdi-map me-1"></i> View on Google Maps
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="mdi mdi-account-plus me-2"></i>Add New Staff</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('warehouse.stores.staff.store', $store->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role</label>
                            <select name="store_role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Date Picker
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
        });

        // Sales Chart
        var salesOptions = {
            series: [{
                name: 'Sales',
                data: @json($analytics['sales_trends']['data'])
            }],
            chart: { type: 'area', height: 300, toolbar: { show: false } },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            xaxis: { categories: @json($analytics['sales_trends']['labels']) },
            colors: ['#0d6efd']
        };
        new ApexCharts(document.querySelector("#salesChart"), salesOptions).render();

        // Category Chart
        var catLabels = @json($analytics['categories']->pluck('name'));
        var catData = @json($analytics['categories']->pluck('total_qty'));
        
        var catOptions = {
            series: catData.length ? catData : [1], 
            labels: catLabels.length ? catLabels : ['No Data'],
            chart: { type: 'donut', height: 250 },
            colors: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384'],
            legend: { position: 'bottom' }
        };
        new ApexCharts(document.querySelector("#categoryChart"), catOptions).render();
    </script>
    @endpush
</x-app-layout>