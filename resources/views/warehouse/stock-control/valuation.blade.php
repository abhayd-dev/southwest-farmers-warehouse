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
            {{-- Total System Value --}}
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 bg-white position-relative border-bottom border-4 border-danger">
                        <div class="position-absolute top-0 end-0 opacity-10 p-3">
                            <i class="mdi mdi-cash-multiple text-danger" style="font-size: 4rem;"></i>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div
                                class="avatar-sm rounded bg-danger bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-cash-multiple text-danger fs-4"></i>
                            </div>
                            <h6 class="text-muted text-uppercase fw-bold mb-0">Total System Value</h6>
                        </div>

                        <h3 class="fw-bold text-dark mb-1">$ {{ number_format($totalValue, 2) }}</h3>

                        <small class="text-muted">
                            <i class="mdi mdi-warehouse me-1"></i> Warehouse + Stores
                        </small>
                    </div>
                </div>
            </div>

            {{-- Warehouse Value --}}
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 bg-white position-relative border-bottom border-4 border-info">
                        <div class="d-flex align-items-center mb-3">
                            <div
                                class="avatar-sm rounded bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3">
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
                            <div
                                class="avatar-sm rounded bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-store text-success fs-4"></i>
                            </div>
                            <h6 class="text-muted text-uppercase fw-bold mb-0">Stores Value</h6>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">$ {{ number_format($storesValue, 2) }}</h3>
                        <small class="text-muted">Distributed Stock</small>
                    </div>
                </div>
            </div>

            {{-- Total Units --}}
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 bg-white position-relative border-bottom border-4 border-warning">
                        <div class="d-flex align-items-center mb-3">
                            <div
                                class="avatar-sm rounded bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-package-variant-closed text-warning fs-4"></i>
                            </div>
                            <h6 class="text-muted text-uppercase fw-bold mb-0">Total Units</h6>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">{{ number_format($totalQty, 0) }}</h3>
                        <small class="text-muted">Across all channels</small>
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
                                <button class="nav-link active fw-bold" data-bs-toggle="tab"
                                    data-bs-target="#productValuation">
                                    <i class="mdi mdi-cube-outline me-1"></i> Product-wise Analytics
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#storeValuation">
                                    <i class="mdi mdi-store-outline me-1"></i> Store-wise Analytics
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-0">
                        <div class="tab-content">

                            {{-- TAB 1: PRODUCT VALUATION --}}
                            <div class="tab-pane fade show active" id="productValuation">
                                <div class="p-4 border-bottom bg-light bg-opacity-50">
                                    <div class="row g-3">
                                        {{-- DEPARTMENT FILTER (Added) --}}
                                        <div class="col-md-3">
                                            <label class="form-label text-muted small fw-bold">DEPARTMENT</label>
                                            <select id="departmentFilter" class="form-select shadow-none">
                                                <option value="">All Departments</option>
                                                @foreach ($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label text-muted small fw-bold">CATEGORY</label>
                                            <select id="categoryFilter" class="form-select shadow-none">
                                                <option value="">All Categories</option>
                                                @foreach ($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label text-muted small fw-bold">STORE</label>
                                            <select id="storeFilter" class="form-select shadow-none">
                                                <option value="">All Stores</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->store_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3 d-flex align-items-end">
                                            <button class="btn btn-dark w-100 shadow-sm" id="applyValuationFilters">
                                                <i class="mdi mdi-filter me-1"></i> Filter Data
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="productValuationTable" class="table table-hover align-middle mb-0"
                                        style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 text-uppercase text-muted small fw-bold">Product</th>
                                                <th class="text-uppercase text-muted small fw-bold">SKU</th>
                                                <th class="text-uppercase text-muted small fw-bold">Department</th>
                                                {{-- Added --}}
                                                <th class="text-center text-uppercase text-muted small fw-bold">Whse
                                                    Qty</th>
                                                <th class="text-uppercase text-muted small fw-bold">Whse Value</th>
                                                <th class="text-center text-uppercase text-muted small fw-bold">Stores
                                                    Qty</th>
                                                <th class="text-uppercase text-muted small fw-bold">Stores Value</th>
                                                <th class="text-end text-uppercase text-muted small fw-bold">Total
                                                    Value</th>
                                                <th class="text-end pe-4 text-uppercase text-muted small fw-bold">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            {{-- TAB 2: STORE VALUATION --}}
                            <div class="tab-pane fade" id="storeValuation">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 text-uppercase text-muted small fw-bold">Store Name
                                                </th>
                                                <th class="text-uppercase text-muted small fw-bold">Location</th>
                                                <th class="text-center text-uppercase text-muted small fw-bold">Total
                                                    Stock Qty</th>
                                                <th class="text-uppercase text-muted small fw-bold">Total Value</th>
                                                <th class="text-uppercase text-muted small fw-bold">Avg. Value / Unit
                                                </th>
                                                <th class="text-center text-uppercase text-muted small fw-bold">Status
                                                </th>
                                                <th class="text-end pe-4 text-uppercase text-muted small fw-bold">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($stores as $store)
                                                @php
                                                    $storeValue = \App\Models\StoreStock::where(
                                                        'store_stocks.store_id',
                                                        $store->id,
                                                    )
                                                        ->join(
                                                            'products',
                                                            'store_stocks.product_id',
                                                            '=',
                                                            'products.id',
                                                        )
                                                        ->sum(
                                                            \Illuminate\Support\Facades\DB::raw(
                                                                'store_stocks.quantity * products.cost_price',
                                                            ),
                                                        );

                                                    $storeQty = \App\Models\StoreStock::where(
                                                        'store_stocks.store_id',
                                                        $store->id,
                                                    )->sum('quantity');
                                                    $avgValue = $storeQty > 0 ? $storeValue / $storeQty : 0;
                                                @endphp
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <div
                                                                class="avatar-sm bg-light rounded d-flex align-items-center justify-content-center me-2 text-primary">
                                                                <i class="mdi mdi-store"></i>
                                                            </div>
                                                            <span
                                                                class="fw-bold text-dark">{{ $store->store_name }}</span>
                                                        </div>
                                                    </td>
                                                    <td><span class="text-muted">{{ $store->city ?? 'N/A' }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-light text-dark border">{{ number_format($storeQty, 0) }}</span>
                                                    </td>
                                                    <td><span class="fw-bold text-primary">$
                                                            {{ number_format($storeValue, 2) }}</span></td>
                                                    <td><span class="text-muted">$
                                                            {{ number_format($avgValue, 2) }}</span></td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge bg-success bg-opacity-10 text-success px-2 py-1">Active</span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <a href="{{ route('warehouse.stock-control.valuation.store-analytics', $store->id) }}"
                                                            class="btn btn-sm btn-outline-dark shadow-sm">
                                                            <i class="mdi mdi-chart-line me-1"></i> Analytics
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5 text-muted">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <i class="mdi mdi-store-off fs-1 opacity-50 mb-2"></i>
                                                            <span>No active stores found with inventory data.</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
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
                let table = $('#productValuationTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '{{ route('warehouse.stock-control.valuation.data') }}',
                        data: function(d) {
                            d.department_id = $('#departmentFilter').val(); // Added Department param
                            d.category_id = $('#categoryFilter').val();
                            d.store_id = $('#storeFilter').val();
                        }
                    },
                    columns: [{
                            data: 'product_name',
                            className: 'ps-4 fw-semibold'
                        },
                        {
                            data: 'sku',
                            className: 'text-muted small'
                        },
                        {
                            data: 'department_name',
                            name: 'department.name',
                            defaultContent: '-'
                        }, // Added Department Column
                        {
                            data: 'warehouse_qty',
                            className: 'text-center'
                        },
                        {
                            data: 'warehouse_value_fmt',
                            className: 'text-muted text-end'
                        },
                        {
                            data: 'stores_qty',
                            className: 'text-center'
                        },
                        {
                            data: 'stores_value_fmt',
                            className: 'text-muted text-end'
                        },
                        {
                            data: 'total_value_fmt',
                            className: 'text-end fw-bold text-success'
                        },
                        {
                            data: 'action',
                            className: 'text-end pe-4',
                            searchable: false,
                            orderable: false
                        }
                    ],
                    order: [
                        [7, 'desc']
                    ], // Order by Total Value descending (Index adjusted for new column)
                    language: {
                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                    }
                });

                $('#applyValuationFilters').click(() => table.draw());
            });
        </script>
    @endpush

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    @endpush

</x-app-layout>
