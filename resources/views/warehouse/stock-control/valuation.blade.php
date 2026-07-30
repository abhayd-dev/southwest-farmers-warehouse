<x-app-layout title="Stock Valuation">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Stock Valuation',
            'items' => [['text' => 'Stock Control', 'url' => route('warehouse.stock-control.overview')]],
        ])

        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-finance text-primary me-2"></i> Stock Valuation
                </h4>
                <small class="text-muted">Real-time inventory valuation across all locations</small>
            </div>
            <button class="btn btn-outline-primary" onclick="window.location.reload()">
                <i class="mdi mdi-refresh me-1"></i> Refresh Data
            </button>
        </div>

        <div class="row g-4 mb-4">
            {{-- Warehouse Value --}}
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 bg-white position-relative border-bottom border-4 border-info">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm rounded bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-warehouse text-info fs-4"></i>
                            </div>
                            <h6 class="text-muted text-uppercase fw-bold mb-0">Warehouse Value</h6>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">$ {{ number_format($warehouseValue, 2) }}</h3>
                        <small class="text-muted">Central Inventory</small>
                    </div>
                </div>
            </div>

            {{-- Stores Value --}}
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 bg-white position-relative border-bottom border-4 border-success">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm rounded bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-store text-success fs-4"></i>
                            </div>
                            <h6 class="text-muted text-uppercase fw-bold mb-0">Stores Value</h6>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">$ {{ number_format($storesValue, 2) }}</h3>
                        <small class="text-muted">Distributed Stock</small>
                    </div>
                </div>
            </div>

            {{-- Total Warehouse Units --}}
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 bg-white position-relative border-bottom border-4 border-primary">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-package-variant-closed text-primary fs-4"></i>
                            </div>
                            <h6 class="text-muted text-uppercase fw-bold mb-0">Total Warehouse Units</h6>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ number_format($totalWarehouseUnits, 0) }}</h3>
                        <small class="text-muted">Warehouse Inventory</small>
                    </div>
                </div>
            </div>

            {{-- Total Store Units --}}
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 bg-white position-relative border-bottom border-4 border-warning">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm rounded bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-storefront text-warning fs-4"></i>
                            </div>
                            <h6 class="text-muted text-uppercase fw-bold mb-0">Total Store Units</h6>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ number_format($totalStoreUnits, 0) }}</h3>
                        <small class="text-muted">Distributed Channel Units</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <ul class="nav nav-pills card-header-pills" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold" id="whse-tab" data-bs-toggle="tab" data-bs-target="#whseAnalytics">
                                    <i class="mdi mdi-warehouse me-1"></i> Warehouse Analytics
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" id="store-tab" data-bs-toggle="tab" data-bs-target="#storeAnalytics">
                                    <i class="mdi mdi-store-outline me-1"></i> Store Analytics
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-0">
                        <div class="tab-content">

                            {{-- TAB 1: WAREHOUSE ANALYTICS --}}
                            <div class="tab-pane fade show active" id="whseAnalytics">
                                <div class="p-4 border-bottom bg-light bg-opacity-50">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">DEPARTMENT</label>
                                            <select id="whseDepartmentFilter" class="form-select shadow-none">
                                                <option value="">All Departments</option>
                                                @foreach ($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">CATEGORY</label>
                                            <select id="whseCategoryFilter" class="form-select shadow-none">
                                                <option value="">All Categories</option>
                                                @foreach ($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <button class="btn btn-dark w-100 shadow-sm" id="applyWhseFilters">
                                                <i class="mdi mdi-filter me-1"></i> Filter Data
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="whseValuationTable" class="table table-hover align-middle mb-0" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 text-uppercase text-muted small fw-bold">Product</th>
                                                <th class="text-uppercase text-muted small fw-bold">UPC</th>
                                                <th class="text-uppercase text-muted small fw-bold">Department</th>
                                                <th class="text-center text-uppercase text-muted small fw-bold">Whse Qty</th>
                                                <th class="text-end text-uppercase text-muted small fw-bold">Whse Cost Value</th>
                                                <th class="text-end text-uppercase text-muted small fw-bold">Total Whse Value</th>
                                                <th class="text-end pe-4 text-uppercase text-muted small fw-bold">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            {{-- TAB 2: STORE ANALYTICS --}}
                            <div class="tab-pane fade" id="storeAnalytics">
                                <div class="p-4 border-bottom bg-light bg-opacity-50">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">SELECT STORE / LOCATION</label>
                                            <select id="storeLocationFilter" class="form-select shadow-none">
                                                <option value="">All Stores</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">DEPARTMENT</label>
                                            <select id="storeDepartmentFilter" class="form-select shadow-none">
                                                <option value="">All Departments</option>
                                                @foreach ($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">SEARCH PRODUCT / UPC</label>
                                            <input type="text" id="storeSearchInput" class="form-control shadow-none" placeholder="Type product name or UPC...">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="storeValuationTable" class="table table-hover align-middle mb-0" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 text-uppercase text-muted small fw-bold">Product</th>
                                                <th class="text-uppercase text-muted small fw-bold">UPC</th>
                                                <th class="text-uppercase text-muted small fw-bold">Department</th>
                                                <th class="text-center text-uppercase text-muted small fw-bold">Store Qty</th>
                                                <th class="text-end text-uppercase text-muted small fw-bold">Store Cost Value</th>
                                                <th class="text-end text-uppercase text-muted small fw-bold">Store Value</th>
                                                <th class="text-end pe-4 text-uppercase text-muted small fw-bold">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(function() {
                // TAB 1: Warehouse Analytics Table
                let whseTable = $('#whseValuationTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '{{ route('warehouse.stock-control.valuation.data') }}',
                        data: function(d) {
                            d.view_type = 'warehouse';
                            d.department_id = $('#whseDepartmentFilter').val();
                            d.category_id = $('#whseCategoryFilter').val();
                        }
                    },
                    columns: [
                        { data: 'product_name', className: 'ps-4 fw-semibold' },
                        { data: 'upc', className: 'text-muted small' },
                        { data: 'department_name', name: 'department.name', defaultContent: '-' },
                        { data: 'warehouse_qty', className: 'text-center' },
                        { data: 'whse_cost_value_fmt', className: 'text-end text-muted' },
                        { data: 'warehouse_value_fmt', className: 'text-end fw-bold text-primary' },
                        { data: 'action', className: 'text-end pe-4', searchable: false, orderable: false }
                    ],
                    order: [[5, 'desc']],
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                    }
                });

                $('#applyWhseFilters').click(() => whseTable.draw());

                // TAB 2: Store Analytics Table
                let storeTable = $('#storeValuationTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '{{ route('warehouse.stock-control.valuation.data') }}',
                        data: function(d) {
                            d.view_type = 'store';
                            d.store_id = $('#storeLocationFilter').val();
                            d.department_id = $('#storeDepartmentFilter').val();
                            d.search_term = $('#storeSearchInput').val();
                        }
                    },
                    columns: [
                        { data: 'product_name', className: 'ps-4 fw-semibold' },
                        { data: 'upc', className: 'text-muted small' },
                        { data: 'department_name', name: 'department.name', defaultContent: '-' },
                        { data: 'stores_qty', className: 'text-center' },
                        { data: 'whse_cost_value_fmt', className: 'text-end text-muted' },
                        { data: 'stores_value_fmt', className: 'text-end fw-bold text-success' },
                        { data: 'action', className: 'text-end pe-4', searchable: false, orderable: false }
                    ],
                    order: [[5, 'desc']],
                    language: {
                        processing: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>'
                    }
                });

                $('#storeLocationFilter, #storeDepartmentFilter').change(() => storeTable.draw());
                
                let searchTimer;
                $('#storeSearchInput').on('keyup input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => storeTable.draw(), 300);
                });
            });
        </script>
    @endpush

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    @endpush

</x-app-layout>
