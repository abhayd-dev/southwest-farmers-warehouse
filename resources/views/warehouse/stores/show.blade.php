<x-app-layout title="Store Dashboard - {{ $store->store_name }}">
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
        <style>
            .stat-card { transition: transform 0.2s; border: none; }
            .stat-card:hover { transform: translateY(-5px); }
            .chart-container { position: relative; height: 320px; width: 100%; }
            .bg-gradient-primary { background: linear-gradient(45deg, #4e73df 0%, #224abe 100%); color: white; }
            .bg-gradient-success { background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%); color: white; }
            .bg-gradient-info { background: linear-gradient(45deg, #36b9cc 0%, #258391 100%); color: white; }
            .bg-gradient-warning { background: linear-gradient(45deg, #f6c23e 0%, #dda20a 100%); color: white; }
            .filter-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; margin-bottom: 0.25rem; }
        </style>
    @endpush

    <div class="container-fluid px-4 py-4">
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded-3 d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px;">
                        <i class="mdi mdi-store fs-1"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">{{ $store->store_name }}</h4>
                        <div class="d-flex align-items-center text-muted small">
                            <span class="me-3"><i class="mdi mdi-barcode me-1"></i> {{ $store->store_code }}</span>
                            <span class="me-3"><i class="mdi mdi-map-marker me-1"></i> {{ $store->city }}, {{ $store->state }}</span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">Active Store</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-outline-primary">
                        <i class="mdi mdi-pencil me-1"></i> Edit Details
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase text-muted fw-bold small mb-1">Total Inventory Value</h6>
                            <h3 class="mb-0 fw-bold text-dark">${{ number_format($stats['inventory_value']) }}</h3>
                        </div>
                        <div class="avatar-md bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-currency-usd fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase text-muted fw-bold small mb-1">Total Products</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($stats['inventory_items']) }}</h3>
                        </div>
                        <div class="avatar-md bg-gradient-info rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-package-variant fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase text-muted fw-bold small mb-1">Low Stock Alerts</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['low_stock_count'] }}</h3>
                        </div>
                        <div class="avatar-md bg-gradient-warning rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-alert-circle-outline fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-uppercase text-muted fw-bold small mb-1">Total Staff</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['staff_count'] }}</h3>
                        </div>
                        <div class="avatar-md bg-gradient-success rounded-circle d-flex align-items-center justify-content-center">
                            <i class="mdi mdi-account-group fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary"><i class="mdi mdi-chart-box-outline me-2"></i> Sales Analytics</h6>
                <button type="button" id="resetFilters" class="btn btn-sm btn-light text-danger fw-bold border">
                    <i class="mdi mdi-refresh me-1"></i> Reset Filters
                </button>
            </div>
            <div class="card-body bg-light bg-opacity-25">
                <form id="analyticsFilter" class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="mdi mdi-calendar"></i></span>
                            <input type="text" id="date_range" class="form-control bg-white" placeholder="Select Dates">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Product Type</label>
                        <select id="product_type" class="form-select">
                            <option value="all">All Products</option>
                            <option value="warehouse">Warehouse (Global)</option>
                            <option value="store">Store (Local)</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Category</label>
                        <select id="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Subcategory</label>
                        <select id="subcategory_id" class="form-select" disabled>
                            <option value="">Select Category First</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-12">
                        <label class="filter-label">Specific Product</label>
                        <select id="product_id" class="form-control select2">
                            <option value="">Search Product...</option>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->product_name }} ({{ $prod->store_id ? 'Local' : 'Global' }})</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title mb-0 fw-bold">Sales Trend</h6>
                    </div>
                    <div class="card-body">
                        <div id="trendChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title mb-0 fw-bold">Category Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div id="categoryChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title mb-0 fw-bold">Top Performing Products</h6>
                    </div>
                    <div class="card-body">
                        <div id="productChart" class="chart-container" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-primary"><i class="mdi mdi-account-group me-2"></i>Store Staff & Roles</h6>
                    <small class="text-muted">Manage user access for this store</small>
                </div>
                <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="mdi mdi-plus me-1"></i> Add New Staff
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted fw-bold">
                            <tr>
                                <th class="ps-4 py-3">Staff Member</th>
                                <th>Role</th>
                                <th>Contact Info</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffMembers as $staff)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold border border-primary border-opacity-25">
                                                {{ strtoupper(substr($staff->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $staff->name }}</h6>
                                                <small class="text-muted">ID: #{{ $staff->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                            {{ $staff->role ? $staff->role->name : 'No Role' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column small">
                                            <span class="text-dark"><i class="mdi mdi-email-outline me-1 text-muted"></i>{{ $staff->email }}</span>
                                            <span class="text-muted"><i class="mdi mdi-phone-outline me-1 text-muted"></i>{{ $staff->phone ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($staff->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="mdi mdi-check-circle me-1"></i>Active</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if (!$staff->isStoreAdmin())
                                            <form action="{{ route('warehouse.stores.staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this staff member?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Remove User">
                                                    <i class="mdi mdi-trash-can fs-5"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted fs-4" title="No Action Allowed">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="{{ asset('assets/images/no-data.svg') }}" height="60" class="opacity-25 mb-3">
                                        <p class="text-muted mb-0">No staff members found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="mdi mdi-account-plus me-2"></i>Add New Staff</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('warehouse.stores.staff.store', $store->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Assign Role <span class="text-danger">*</span></label>
                            <select name="store_role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const storeId = "{{ $store->id }}";

                // 1. Initialize Plugins
                const datePicker = flatpickr("#date_range", { mode: "range", dateFormat: "Y-m-d" });
                
                $('.select2').select2({
                    theme: "bootstrap-5",
                    placeholder: "Search Product",
                    allowClear: true,
                    width: '100%'
                });

                // 2. Initialize Charts
                const commonChartOptions = {
                    chart: { type: 'area', fontFamily: 'inherit', toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.9, stops: [0, 90, 100] } }
                };

                const trendChart = new ApexCharts(document.querySelector("#trendChart"), {
                    ...commonChartOptions,
                    height: 320,
                    series: [],
                    noData: { text: 'Loading data...' },
                    colors: ['#4e73df']
                });
                trendChart.render();

                const catChart = new ApexCharts(document.querySelector("#categoryChart"), {
                    chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
                    series: [],
                    labels: [],
                    noData: { text: 'Loading data...' },
                    legend: { position: 'bottom' },
                    colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                });
                catChart.render();

                const prodChart = new ApexCharts(document.querySelector("#productChart"), {
                    chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '55%' } },
                    dataLabels: { enabled: false },
                    series: [],
                    xaxis: { categories: [] },
                    noData: { text: 'Loading data...' },
                    colors: ['#36b9cc']
                });
                prodChart.render();

                // 3. Fetch Data Logic
                function fetchData() {
                    const filters = {
                        date_range: document.getElementById('date_range').value,
                        product_type: document.getElementById('product_type').value,
                        category_id: document.getElementById('category_id').value,
                        subcategory_id: document.getElementById('subcategory_id').value,
                        product_id: $('#product_id').val()
                    };

                    fetch(`/warehouse/stores/${storeId}/analytics?` + new URLSearchParams(filters))
                        .then(res => res.json())
                        .then(data => {
                            // Update Trend
                            trendChart.updateOptions({
                                series: [{ name: 'Sales', data: data.sales_trend.data }],
                                xaxis: { categories: data.sales_trend.labels }
                            });

                            // Update Category
                            catChart.updateOptions({
                                series: data.category_distribution.data,
                                labels: data.category_distribution.labels
                            });

                            // Update Product
                            prodChart.updateOptions({
                                series: [{ name: 'Qty Sold', data: data.product_performance.data }],
                                xaxis: { categories: data.product_performance.labels }
                            });
                        })
                        .catch(err => console.error("Error fetching analytics:", err));
                }

                // 4. Cascading Subcategory
                document.getElementById('category_id').addEventListener('change', function() {
                    const catId = this.value;
                    const subSelect = document.getElementById('subcategory_id');
                    subSelect.innerHTML = '<option value="">Loading...</option>';
                    subSelect.disabled = true;

                    if (catId) {
                        fetch("{{ route('warehouse.product-options.fetch-subcategories', ':id') }}".replace(':id', catId))
                            .then(res => res.json())
                            .then(data => {
                                subSelect.innerHTML = '<option value="">All Subcategories</option>';
                                data.forEach(sub => {
                                    subSelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
                                });
                                subSelect.disabled = false;
                            });
                    } else {
                        subSelect.innerHTML = '<option value="">Select Category First</option>';
                    }
                    fetchData();
                });

                // 5. Event Listeners
                ['date_range', 'product_type', 'subcategory_id'].forEach(id => {
                    document.getElementById(id).addEventListener('change', fetchData);
                });
                $('#product_id').on('change', fetchData);

                // 6. Reset Filters Logic
                document.getElementById('resetFilters').addEventListener('click', function() {
                    // Reset Inputs
                    datePicker.clear();
                    document.getElementById('product_type').value = 'all';
                    document.getElementById('category_id').value = '';
                    document.getElementById('subcategory_id').value = '';
                    document.getElementById('subcategory_id').disabled = true;
                    document.getElementById('subcategory_id').innerHTML = '<option value="">Select Category First</option>';
                    
                    // Reset Select2
                    $('#product_id').val(null).trigger('change.select2'); // Important: trigger change.select2 to update UI but NOT fetch yet

                    // Small delay to prevent double fetch from select2 change event
                    setTimeout(fetchData, 100);
                });

                // Initial Load
                fetchData();
            });
        </script>
    @endpush
</x-app-layout>