<x-app-layout title="Store Dashboard - {{ $store->store_name }}">
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container .select2-selection--single {
                height: 38px;
                border-color: #dee2e6;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
            }

            .chart-container {
                position: relative;
                height: 350px;
                width: 100%;
            }
        </style>
    @endpush

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stores.index') }}">Stores</a></li>
                        <li class="breadcrumb-item active">{{ $store->store_code }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">{{ $store->store_name }} <span
                        class="badge bg-success fs-6 align-middle ms-2">Active</span></h4>
                <p class="text-muted mb-0"><i class="mdi mdi-map-marker me-1"></i> {{ $store->city }}
                    ({{ $store->address }})</p>
            </div>
            <div>
                <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-primary shadow-sm">
                    <i class="mdi mdi-pencil me-1"></i> Edit Store
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Inventory Value</h6>
                        <h3 class="mb-0 fw-bold">${{ number_format($stats['inventory_value']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Unique Products</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['inventory_items'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Low Stock Items</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $stats['low_stock_count'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Total Staff</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['staff_count'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Sales Analytics Filter
                </h6>
            </div>
            <div class="card-body">
                <form id="analyticsFilter" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date Range</label>
                        <input type="text" id="date_range" class="form-control" placeholder="Select Dates">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Product Type</label>
                        <select id="product_type" class="form-select">
                            <option value="all">All Products</option>
                            <option value="warehouse">Warehouse (Global)</option>
                            <option value="store">Store (Local)</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Category</label>
                        <select id="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Subcategory</label>
                        <select id="subcategory_id" class="form-select" disabled>
                            <option value="">Select Category First</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Specific Product</label>
                        <select id="product_id" class="form-control select2">
                            <option value="">Search Product...</option>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->product_name }}
                                    ({{ $prod->store_id ? 'Local' : 'Global' }})</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Sales Trend</h5>
                    </div>
                    <div class="card-body">
                        <div id="trendChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Sales by Category</h5>
                    </div>
                    <div class="card-body">
                        <div id="categoryChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Top Performing Products</h5>
                    </div>
                    <div class="card-body">
                        <div id="productChart" class="chart-container" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="mdi mdi-account-group me-2 text-primary"></i>Store Staff</h5>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="mdi mdi-plus me-1"></i> Add Staff
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
                                            <div
                                                class="avatar-sm bg-light text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold">
                                                {{ substr($staff->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $staff->name }}</h6>
                                                <small class="text-muted">{{ $staff->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                            {{ $staff->role ? $staff->role->name : 'No Role' }}
                                        </span>
                                    </td>
                                    <td>{{ $staff->phone ?? '-' }}</td>
                                    <td><span class="badge bg-success-subtle text-success">Active</span></td>
                                    <td class="text-end pe-4">
                                        @if (!$staff->isStoreAdmin())
                                            <form action="{{ route('warehouse.stores.staff.destroy', $staff->id) }}"
                                                method="POST" class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger border-0"><i
                                                        class="mdi mdi-trash-can"></i></button>
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

    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Add New Staff</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('warehouse.stores.staff.store', $store->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label fw-bold">Name</label><input type="text"
                                name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-bold">Email</label><input type="email"
                                name="email" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-bold">Role</label>
                            <select name="store_role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label fw-bold">Password</label><input type="password"
                                name="password" class="form-control" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Add Staff</button>
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
                flatpickr("#date_range", {
                    mode: "range",
                    dateFormat: "Y-m-d"
                });
                $('.select2').select2({
                    placeholder: "Search Product",
                    allowClear: true
                });

                // 2. Initialize Charts (Empty)
                const trendChart = new ApexCharts(document.querySelector("#trendChart"), {
                    chart: {
                        type: 'area',
                        height: 350
                    },
                    series: [],
                    noData: {
                        text: 'Loading...'
                    }
                });
                trendChart.render();

                const catChart = new ApexCharts(document.querySelector("#categoryChart"), {
                    chart: {
                        type: 'donut',
                        height: 350
                    },
                    series: [],
                    labels: [],
                    noData: {
                        text: 'Loading...'
                    }
                });
                catChart.render();

                const prodChart = new ApexCharts(document.querySelector("#productChart"), {
                    chart: {
                        type: 'bar',
                        height: 300
                    },
                    series: [],
                    xaxis: {
                        categories: []
                    },
                    noData: {
                        text: 'Loading...'
                    }
                });
                prodChart.render();

                // 3. Fetch Data Logic
                function fetchData() {
                    const filters = {
                        date_range: document.getElementById('date_range').value,
                        product_type: document.getElementById('product_type').value,
                        category_id: document.getElementById('category_id').value,
                        subcategory_id: document.getElementById('subcategory_id').value,
                        product_id: $('#product_id').val() // Select2 uses jQuery
                    };

                    fetch(`/warehouse/stores/${storeId}/analytics?` + new URLSearchParams(filters))
                        .then(res => res.json())
                        .then(data => {
                            // Update Trend Chart
                            trendChart.updateOptions({
                                series: [{
                                    name: 'Sales',
                                    data: data.sales_trend.data
                                }],
                                xaxis: {
                                    categories: data.sales_trend.labels
                                }
                            });

                            // Update Category Chart
                            catChart.updateOptions({
                                series: data.category_distribution.data,
                                labels: data.category_distribution.labels
                            });

                            // Update Product Chart
                            prodChart.updateOptions({
                                series: [{
                                    name: 'Qty Sold',
                                    data: data.product_performance.data
                                }],
                                xaxis: {
                                    categories: data.product_performance.labels
                                }
                            });
                        });
                }

                // 4. Cascading Subcategory
                document.getElementById('category_id').addEventListener('change', function() {
                    const catId = this.value;
                    const subSelect = document.getElementById('subcategory_id');
                    subSelect.innerHTML = '<option value="">Loading...</option>';
                    subSelect.disabled = true;

                    if (catId) {
                        fetch("{{ route('warehouse.product-options.fetch-subcategories', ':id') }}".replace(
                                ':id', catId))
                            .then(res => res.json())
                            .then(data => {
                                subSelect.innerHTML = '<option value="">All Subcategories</option>';
                                data.forEach(sub => {
                                    subSelect.innerHTML +=
                                        `<option value="${sub.id}">${sub.name}</option>`;
                                });
                                subSelect.disabled = false;
                            });
                    } else {
                        subSelect.innerHTML = '<option value="">Select Category First</option>';
                    }
                    fetchData(); // Refresh charts
                });

                // 5. Event Listeners for Filters
                ['date_range', 'product_type', 'subcategory_id'].forEach(id => {
                    document.getElementById(id).addEventListener('change', fetchData);
                });
                $('#product_id').on('change', fetchData);

                // Initial Load
                fetchData();
            });
        </script>
    @endpush
</x-app-layout>
