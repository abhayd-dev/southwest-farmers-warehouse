<x-app-layout title="Dashboard | Warehouse POS">

    <div class="container-fluid px-3 px-lg-4 py-3">

        {{-- 1. HEADER & DATE FILTER --}}
        <div class="row mb-4 align-items-center justify-content-between g-3">
            {{-- Welcome Text --}}
            <div class="col-12 col-lg-5">
                <h4 class="fw-bold text-dark mb-1">
                    Hello, {{ auth()->user()->name }} 👋
                </h4>
                <p class="text-muted mb-0 small">
                    Overview for <span class="fw-semibold text-primary">{{ $start->format('M d') }} - {{ $end->format('M d, Y') }}</span>
                </p>
            </div>
            
            {{-- Date Filter Form --}}
            <div class="col-12 col-lg-7">
                <form method="GET" class="bg-white p-2 rounded-3 shadow-sm border">
                    <div class="row g-2 align-items-center">
                        <div class="col-6 col-md">
                            <label class="visually-hidden">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm border" value="{{ $start->format('Y-m-d') }}">
                        </div>
                        <div class="col-auto d-none d-md-block text-muted fw-bold">to</div>
                        <div class="col-6 col-md">
                            <label class="visually-hidden">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm border" value="{{ $end->format('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-auto">
                            <button class="btn btn-dark btn-sm w-100 text-nowrap shadow-sm">
                                <i class="mdi mdi-filter-variant me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- 2. KPI METRICS ROW --}}
        <div class="row g-3 mb-4">

            {{-- 1. Valuation --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports')) && isset($data['inventory_value']))
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1 pe-2">
                                <p class="text-muted mb-1 text-uppercase fw-semibold small">Total Asset Value</p>
                                <h4 class="fw-bold text-dark mb-0">${{ number_format($data['inventory_value']) }}</h4>
                            </div>
                            <div class="rounded-circle p-2 bg-success bg-opacity-10 flex-shrink-0">
                                <i class="mdi mdi-currency-usd text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 pt-2 border-top mt-2">
                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 small">
                                <i class="mdi mdi-chart-line me-1"></i>Live
                            </span>
                            <span class="text-muted small">Cost Price</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. Procurement Spend --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports')) && isset($data['po_spend']))
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1 pe-2">
                                <p class="text-muted mb-1 text-uppercase fw-semibold small">PO Spend</p>
                                <h4 class="fw-bold text-dark mb-0">${{ number_format($data['po_spend']) }}</h4>
                            </div>
                            <div class="rounded-circle p-2 bg-primary bg-opacity-10 flex-shrink-0">
                                <i class="mdi mdi-cart-outline text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2">
                            @if($data['po_trend'] >= 0)
                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 small">
                                    <i class="mdi mdi-arrow-up-bold"></i> {{ $data['po_trend'] }}%
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 small">
                                    <i class="mdi mdi-arrow-down-bold"></i> {{ abs($data['po_trend']) }}%
                                </span>
                            @endif
                            <span class="text-muted small">vs previous</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 3. Low Stock --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_dashboard')) && isset($data['low_stock']))
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1 pe-2">
                                <p class="text-muted mb-1 text-uppercase fw-semibold small">Low Stock Alerts</p>
                                <h4 class="fw-bold text-dark mb-0">{{ $data['low_stock'] }}</h4>
                            </div>
                            <div class="rounded-circle p-2 bg-danger bg-opacity-10 flex-shrink-0">
                                <i class="mdi mdi-alert-outline text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="pt-2 border-top mt-2">
                            <a href="{{ route('warehouse.stocks.index') }}" class="btn btn-danger btn-sm w-100 shadow-sm">
                                <i class="mdi mdi-eye-outline me-1"></i>View & Restock
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 4. Pending Orders --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores') || auth()->user()->hasPermission('approve_store_requests')) && isset($data['pending_requests']))
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1 pe-2">
                                <p class="text-muted mb-1 text-uppercase fw-semibold small">Pending Requests</p>
                                <h4 class="fw-bold text-dark mb-0">{{ $data['pending_requests'] }}</h4>
                            </div>
                            <div class="rounded-circle p-2 bg-warning bg-opacity-10 flex-shrink-0">
                                <i class="mdi mdi-truck-delivery-outline text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="pt-2 border-top mt-2">
                            <a href="{{ route('warehouse.stock-requests.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm w-100 shadow-sm">
                                <i class="mdi mdi-clipboard-check-outline me-1"></i>Process Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- 3. CHARTS ROW --}}
        <div class="row g-3 mb-4">
            
            {{-- Stock Movement Chart (Full Width) --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory')) && isset($data['chart_in']))
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="card-title mb-0 fw-bold text-dark">Inventory Flow</h6>
                                <p class="text-muted mb-0 small">Stock In vs Dispatched Overview</p>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                <i class="mdi mdi-chart-areaspline me-1"></i>Analytics
                            </span>
                        </div>
                    </div>
                    <div class="card-body px-3 pb-3 pt-0">
                        <div id="stockFlowChart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 4. WIDGETS ROW (Below Chart, Col-6 each on Desktop, Col-12 on Mobile) --}}
            
            {{-- Top Moving Products Pie Chart --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory')) && isset($data['top_products']) && count($data['top_products']) > 0)
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-title mb-0 fw-bold text-dark">Top Dispatched Items</h6>
                                <p class="text-muted mb-0 small">Most moved products</p>
                            </div>
                            <i class="mdi mdi-chart-donut text-primary fs-5"></i>
                        </div>
                    </div>
                    <div class="card-body px-2 pb-3 pt-1">
                        <div id="topProductsChart" class="d-flex justify-content-center"></div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Recent POs --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_po')) && isset($data['recent_pos']))
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-title mb-0 fw-bold text-dark">Recent POs</h6>
                                <p class="text-muted mb-0 small">Latest purchase orders</p>
                            </div>
                            <a href="{{ route('warehouse.purchase-orders.index') }}" class="btn btn-sm btn-outline-dark px-2 py-1 small">
                                View All <i class="mdi mdi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($data['recent_pos'] as $po)
                            <div class="list-group-item border-0 px-3 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-1 fw-bold text-dark text-truncate small">#{{ $po->po_number }}</h6>
                                        <p class="text-muted mb-2 text-truncate small">{{ $po->vendor->name ?? 'N/A' }}</p>
                                        @php
                                            $badges = [
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'received' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $badges[$po->status] ?? 'secondary' }} bg-opacity-10 text-{{ $badges[$po->status] ?? 'secondary' }} small">
                                            {{ ucfirst($po->status) }}
                                        </span>
                                    </div>
                                    <div class="text-end ms-2 flex-shrink-0">
                                        <h6 class="mb-0 fw-bold text-dark small">${{ number_format($po->total_amount) }}</h6>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- 5. RECENT STORE REQUESTS TABLE --}}
        @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores') || auth()->user()->hasPermission('approve_store_requests')) && isset($data['recent_requests']))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3 px-3">
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                            <div>
                                <h6 class="card-title mb-0 fw-bold text-dark">Latest Store Requests</h6>
                                <p class="text-muted mb-0 small">Recent incoming requests from stores</p>
                            </div>
                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 align-self-start align-self-sm-center">
                                <i class="mdi mdi-format-list-bulleted me-1"></i>{{ count($data['recent_requests']) }} Requests
                            </span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-nowrap">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 py-2 fw-semibold text-muted text-uppercase small">Request ID</th>
                                    <th class="py-2 fw-semibold text-muted text-uppercase small">Store</th>
                                    <th class="py-2 fw-semibold text-muted text-uppercase small">Items</th>
                                    <th class="py-2 fw-semibold text-muted text-uppercase small">Status</th>
                                    <th class="py-2 fw-semibold text-muted text-uppercase small">Requested</th>
                                    <th class="text-end pe-3 py-2 fw-semibold text-muted text-uppercase small">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['recent_requests'] as $req)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <span class="fw-bold text-primary small">#REQ-{{ $req->id }}</span>
                                    </td>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-1 me-2 flex-shrink-0">
                                                <i class="mdi mdi-store text-primary small"></i>
                                            </div>
                                            <span class="fw-semibold text-dark small text-truncate" style="max-width: 150px;">{{ $req->store->store_name ?? 'Unknown Store' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 small">
                                            <i class="mdi mdi-package-variant me-1"></i>{{ $req->requested_quantity }} units
                                        </span>
                                    </td>
                                    <td class="py-2">
                                        @if($req->status == 'pending')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 small">
                                                <i class="mdi mdi-clock-outline me-1"></i>Pending
                                            </span>
                                        @elseif($req->status == 'approved')
                                            <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 small">
                                                <i class="mdi mdi-check-circle-outline me-1"></i>Approved
                                            </span>
                                        @elseif($req->status == 'completed')
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 small">
                                                <i class="mdi mdi-check-all me-1"></i>Completed
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 small">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-muted small">{{ $req->created_at->diffForHumans() }}</td>
                                    <td class="text-end pe-3 py-2">
                                        <a href="{{ route('warehouse.stock-requests.show', $req->id) }}" class="btn btn-sm btn-dark shadow-sm px-3 py-1 small">
                                            <i class="mdi mdi-eye-outline me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- SCRIPTS --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // 1. Stock Movement Area Chart
        @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory')) && isset($data['chart_in']))
        var movementOptions = {
            series: [{
                name: 'Stock In (Purchase/Returns)',
                data: @json($data['chart_in'])
            }, {
                name: 'Stock Out (Dispatched/Sales)',
                data: @json($data['chart_out'])
            }],
            chart: {
                type: 'area',
                height: 300,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false },
                parentHeightOffset: 0,
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            xaxis: {
                categories: @json($data['chart_dates']),
                type: 'datetime',
                labels: { 
                    format: 'dd MMM',
                    style: {
                        fontSize: '11px',
                        cssClass: 'text-muted'
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '11px',
                        cssClass: 'text-muted'
                    }
                }
            },
            colors: ['#0ab39c', '#f06548'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] }
            },
            grid: { 
                borderColor: '#f1f1f1', 
                strokeDashArray: 4,
                padding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 10
                }
            },
            tooltip: { x: { format: 'dd MMM yyyy' } },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                offsetY: -20,
                fontSize: '12px'
            }
        };

        var movementChart = new ApexCharts(document.querySelector("#stockFlowChart"), movementOptions);
        movementChart.render();
        @endif

        // 2. Top Products Pie Chart
        @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory')) && isset($data['top_products']) && count($data['top_products']) > 0)
        var pieOptions = {
            series: @json($data['top_products']->pluck('qty')),
            labels: @json($data['top_products']->pluck('product_name')),
            chart: {
                type: 'donut',
                height: 260,
                fontFamily: 'inherit',
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '14px',
                                fontFamily: 'inherit',
                                fontWeight: 500,
                                color: '#888',
                                offsetY: -5
                            },
                            value: {
                                show: true,
                                fontSize: '20px',
                                fontFamily: 'inherit',
                                fontWeight: 700,
                                color: '#333',
                                offsetY: 5,
                                formatter: function (val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '13px',
                                fontFamily: 'inherit',
                                fontWeight: 600,
                                color: '#373d3f',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                        return a + b
                                    }, 0)
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            stroke: { show: false },
            legend: { 
                position: 'bottom',
                offsetY: 0,
                height: 50,
                fontSize: '12px',
                markers: {
                    width: 8,
                    height: 8,
                    radius: 12
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 5
                }
            },
            colors: ['#556ee6', '#f1b44c', '#34c38f', '#f46a6a', '#50a5f1'],
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return val + " Units";
                    }
                }
            }
        };

        var pieChart = new ApexCharts(document.querySelector("#topProductsChart"), pieOptions);
        pieChart.render();
        @endif
    </script>
    @endpush

</x-app-layout>