<x-app-layout title="Store Dashboard - {{ $store->store_name }}">
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
            rel="stylesheet" />
        <style>
            .stat-card {
                border: none;
                border-radius: 10px;
                transition: all 0.3s ease;
            }

            .stat-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .chart-container {
                position: relative;
                height: 320px;
                width: 100%;
            }

            .bg-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .bg-gradient-success {
                background: linear-gradient(135deg, #2af598 0%, #009efd 100%);
                color: white;
            }

            .bg-gradient-warning {
                background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
                color: white;
            }

            .bg-gradient-info {
                background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
                color: white;
            }

            .filter-label {
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                color: #adb5bd;
                letter-spacing: 0.5px;
            }
        </style>
    @endpush

    <div class="container-fluid px-4 py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">{{ $store->store_name }}</h4>
                <p class="text-muted mb-0 small">
                    <i class="mdi mdi-map-marker me-1"></i> {{ $store->city }} ({{ $store->store_code }})
                    <span class="mx-2">|</span>
                    <span
                        class="badge {{ $store->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                        {{ $store->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
            <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-outline-primary btn-sm shadow-sm">
                <i class="mdi mdi-pencil me-1"></i> Edit Store
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div
                            class="avatar-md bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                            <i class="mdi mdi-currency-usd fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Inventory Value</h6>
                            <h3 class="mb-0 fw-bold text-dark">${{ number_format($stats['inventory_value']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div
                            class="avatar-md bg-gradient-info rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                            <i class="mdi mdi-package-variant fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Products</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ number_format($stats['inventory_items']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div
                            class="avatar-md bg-gradient-warning rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                            <i class="mdi mdi-alert-circle-outline fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Low Stock</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['low_stock_count'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div
                            class="avatar-md bg-gradient-success rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm">
                            <i class="mdi mdi-account-group fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold mb-0">Total Staff</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['staff_count'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-primary"><i class="mdi mdi-chart-timeline me-2"></i> Sales
                    Analytics</h6>
                <button type="button" id="resetFilters" class="btn btn-sm btn-light border text-danger fw-bold">
                    <i class="mdi mdi-refresh me-1"></i> Reset Filters
                </button>
            </div>
            <div class="card-body bg-light bg-opacity-10">
                <form id="analyticsFilter" class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label">Date Range</label>
                        <input type="text" id="date_range" class="form-control form-control-sm bg-white"
                            placeholder="Select dates...">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Product Type</label>
                        <select id="product_type" class="form-select form-select-sm">
                            <option value="all">All Products</option>
                            <option value="warehouse">Warehouse (Global)</option>
                            <option value="store">Store (Local)</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Category</label>
                        <select id="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Subcategory</label>
                        <select id="subcategory_id" class="form-select form-select-sm" disabled>
                            <option value="">Select Category First</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-12">
                        <label class="filter-label">Specific Product</label>
                        <select id="product_id" class="form-control select2">
                            <option value="">Search product...</option>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Sales Trend</h6>
                        <div id="trendChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Category Distribution</h6>
                        <div id="categoryChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Top 10 Best Selling Products</h6>
                        <div id="productChart" class="chart-container" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold"><i class="mdi mdi-account-group me-2"></i>Store Staff</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="mdi mdi-plus me-1"></i> Add Staff
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted text-uppercase small">
                            <tr>
                                <th class="ps-4">Staff Member</th>
                                <th>Role</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffMembers as $staff)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="avatar-sm bg-light text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold border">
                                                {{ substr($staff->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $staff->name }}</h6>
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
                                                method="POST" 
                                                class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button
                                                    class="btn btn-sm btn-outline-danger border-0 rounded-circle"><i
                                                        class="mdi mdi-trash-can"></i></button>
                                            </form>
                                        @else
                                            <span class="text-muted px-2" title="Cannot remove Admin">-</span>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Add Staff Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('warehouse.stores.staff.store', $store->id) }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Full Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Role <span
                                    class="text-danger">*</span></label>
                            <select name="store_role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Create Account</button>
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

                // Plugins
                const datePicker = flatpickr("#date_range", {
                    mode: "range",
                    dateFormat: "Y-m-d"
                });
                $('.select2').select2({
                    theme: "bootstrap-5",
                    width: '100%',
                    allowClear: true
                });

                // Charts
                const options = {
                    chart: {
                        type: 'area',
                        height: 320,
                        fontFamily: 'inherit',
                        toolbar: {
                            show: false
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    dataLabels: {
                        enabled: false
                    }
                };
                const trendChart = new ApexCharts(document.querySelector("#trendChart"), {
                    ...options,
                    series: [],
                    colors: ['#667eea'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.6,
                            opacityTo: 0.1
                        }
                    }
                });
                trendChart.render();

                const catChart = new ApexCharts(document.querySelector("#categoryChart"), {
                    chart: {
                        type: 'donut',
                        height: 320,
                        fontFamily: 'inherit'
                    },
                    series: [],
                    labels: [],
                    legend: {
                        position: 'bottom'
                    },
                    colors: ['#667eea', '#764ba2', '#2af598', '#f6d365', '#ff6b6b']
                });
                catChart.render();

                const prodChart = new ApexCharts(document.querySelector("#productChart"), {
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [],
                    xaxis: {
                        categories: []
                    },
                    colors: ['#89f7fe']
                });
                prodChart.render();

                function fetchData() {
                    const params = new URLSearchParams({
                        date_range: document.getElementById('date_range').value,
                        product_type: document.getElementById('product_type').value,
                        category_id: document.getElementById('category_id').value,
                        subcategory_id: document.getElementById('subcategory_id').value,
                        product_id: $('#product_id').val()
                    });

                    fetch(`/warehouse/stores/${storeId}/analytics?` + params)
                        .then(res => res.json())
                        .then(data => {
                            trendChart.updateOptions({
                                series: [{
                                    name: 'Sales',
                                    data: data.sales_trend.data
                                }],
                                xaxis: {
                                    categories: data.sales_trend.labels
                                }
                            });
                            catChart.updateOptions({
                                series: data.category_distribution.data,
                                labels: data.category_distribution.labels
                            });
                            prodChart.updateOptions({
                                series: [{
                                    name: 'Qty',
                                    data: data.product_performance.data
                                }],
                                xaxis: {
                                    categories: data.product_performance.labels
                                }
                            });
                        });
                }

                // Events
                ['date_range', 'product_type', 'subcategory_id'].forEach(id => document.getElementById(id)
                    .addEventListener('change', fetchData));
                $('#product_id').on('change', fetchData);

                // Cascading
                document.getElementById('category_id').addEventListener('change', function() {
                    const id = this.value;
                    const sub = document.getElementById('subcategory_id');
                    sub.disabled = true;
                    sub.innerHTML = '<option>Loading...</option>';

                    if (id) {
                        fetch("{{ route('warehouse.product-options.fetch-subcategories', ':id') }}".replace(
                                ':id', id))
                            .then(r => r.json())
                            .then(d => {
                                sub.innerHTML = '<option value="">All Subcategories</option>' + d.map(s =>
                                    `<option value="${s.id}">${s.name}</option>`).join('');
                                sub.disabled = false;
                            });
                    } else {
                        sub.innerHTML = '<option>Select Category First</option>';
                    }
                    fetchData();
                });

                // Reset
                document.getElementById('resetFilters').addEventListener('click', () => {
                    datePicker.clear();
                    document.getElementById('product_type').value = 'all';
                    document.getElementById('category_id').value = '';
                    document.getElementById('subcategory_id').innerHTML =
                        '<option>Select Category First</option>';
                    document.getElementById('subcategory_id').disabled = true;
                    $('#product_id').val(null).trigger('change');
                    setTimeout(fetchData, 100);
                });

                fetchData();
            });
        </script>
    @endpush
</x-app-layout>
