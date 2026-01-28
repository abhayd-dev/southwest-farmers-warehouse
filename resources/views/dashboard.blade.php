<x-app-layout title="Dashboard | Warehouse POS">

    <div class="container-fluid">

        {{-- 1. HEADER & DATE FILTER (Visible to All) --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 mt-2">
            <div>
                <h4 class="fw-bold text-dark mb-0">
                    Hello, {{ auth()->user()->name }}
                </h4>
                <span class="text-muted small">
                    Overview for <span class="fw-bold text-primary">{{ $start->format('M d') }} - {{ $end->format('M d, Y') }}</span>
                </span>
            </div>
            
            <form method="GET" class="d-flex gap-2 bg-white p-1 rounded shadow-sm border mt-3 mt-md-0">
                <input type="date" name="start_date" class="form-control form-control-sm border-0" value="{{ $start->format('Y-m-d') }}">
                <span class="align-self-center text-muted fw-bold">-</span>
                <input type="date" name="end_date" class="form-control form-control-sm border-0" value="{{ $end->format('Y-m-d') }}">
                <button class="btn btn-sm btn-dark px-3">Filter</button>
            </form>
        </div>

        {{-- 2. KPI METRICS ROW --}}
        <div class="row g-3 mb-4">

            {{-- 1. Valuation (Permission: view_financial_reports) --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports')) && isset($data['inventory_value']))
            <div class="col-md-6 col-xxl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fw-bold small">Total Asset Value</p>
                                <h3 class="fw-bold text-dark mb-0">${{ number_format($data['inventory_value']) }}</h3>
                            </div>
                            <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-currency-usd fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-success-subtle text-success">Live Valuation</span>
                            <span class="text-muted small ms-1">Based on Cost Price</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. Procurement Spend (Permission: view_financial_reports) --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports')) && isset($data['po_spend']))
            <div class="col-md-6 col-xxl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fw-bold small">PO Spend (Selected Period)</p>
                                <h3 class="fw-bold text-dark mb-0">${{ number_format($data['po_spend']) }}</h3>
                            </div>
                            <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-cart-outline fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            @if($data['po_trend'] >= 0)
                                <span class="text-danger fw-bold"><i class="mdi mdi-arrow-up"></i> {{ $data['po_trend'] }}%</span>
                            @else
                                <span class="text-success fw-bold"><i class="mdi mdi-arrow-down"></i> {{ abs($data['po_trend']) }}%</span>
                            @endif
                            <span class="text-muted small">vs previous period</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 3. Low Stock (Permission: view_dashboard OR view_inventory) --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_dashboard')) && isset($data['low_stock']))
            <div class="col-md-6 col-xxl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fw-bold small">Low Stock Alerts</p>
                                <h3 class="fw-bold text-dark mb-0">{{ $data['low_stock'] }}</h3>
                            </div>
                            <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-alert-outline fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('warehouse.stocks.index') }}" class="small text-danger fw-bold text-decoration-none">
                                View & Restock <i class="mdi mdi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 4. Pending Orders (Permission: view_stores OR approve_store_requests) --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores') || auth()->user()->hasPermission('approve_store_requests')) && isset($data['pending_requests']))
            <div class="col-md-6 col-xxl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fw-bold small">Pending Store Requests</p>
                                <h3 class="fw-bold text-dark mb-0">{{ $data['pending_requests'] }}</h3>
                            </div>
                            <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-truck-delivery-outline fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('warehouse.stock-requests.index', ['status' => 'pending']) }}" class="small text-warning fw-bold text-decoration-none">
                                Process Orders <i class="mdi mdi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <div class="row g-3">
            
            {{-- 3. STOCK MOVEMENT CHART (Permission: view_inventory) --}}
            @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory')) && isset($data['chart_in']))
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title mb-0 fw-bold">Inventory Flow (Stock In vs Dispatched)</h6>
                    </div>
                    <div class="card-body">
                        <div id="stockFlowChart" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 4. SIDE WIDGETS --}}
            <div class="col-xl-4">
                
                {{-- Top Moving Products (PIE CHART) (Permission: view_inventory) --}}
                @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory')) && isset($data['top_products']) && count($data['top_products']) > 0)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title mb-0 fw-bold">Top Dispatched Items</h6>
                    </div>
                    <div class="card-body">
                        {{-- Chart Container --}}
                        <div id="topProductsChart" class="d-flex justify-content-center align-items-center" style="min-height: 300px;"></div>
                    </div>
                </div>
                @endif

                {{-- Recent POs (Permission: view_po) --}}
                @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_po')) && isset($data['recent_pos']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold">Recent POs</h6>
                        <a href="{{ route('warehouse.purchase-orders.index') }}" class="small text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    @foreach($data['recent_pos'] as $po)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark small">#{{ $po->po_number }}</div>
                                            <div class="text-muted small" style="font-size: 11px;">{{ $po->vendor->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="fw-bold text-dark small">${{ number_format($po->total_amount) }}</div>
                                            @php
                                                $badges = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'received' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $badges[$po->status] ?? 'secondary' }}-subtle text-{{ $badges[$po->status] ?? 'secondary' }} small" style="font-size: 10px;">{{ ucfirst($po->status) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- 5. RECENT STORE REQUESTS TABLE (Permission: view_stores OR approve_store_requests) --}}
        @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores') || auth()->user()->hasPermission('approve_store_requests')) && isset($data['recent_requests']))
        <div class="row mt-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="card-title mb-0 fw-bold">Latest Store Requests</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Request ID</th>
                                    <th>Store</th>
                                    <th>Items Requested</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['recent_requests'] as $req)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#REQ-{{ $req->id }}</td>
                                    <td class="fw-bold">{{ $req->store->store_name ?? 'Unknown Store' }}</td>
                                    <td>
                                        <i class="mdi mdi-package-variant me-1"></i> 
                                        {{ $req->requested_quantity }} units
                                    </td>
                                    <td>
                                        @if($req->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($req->status == 'approved')
                                            <span class="badge bg-info">Approved</span>
                                        @elseif($req->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($req->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $req->created_at->diffForHumans() }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('warehouse.stock-requests.show', $req->id) }}" class="btn btn-sm btn-outline-dark">View</a>
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
                height: 350,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: @json($data['chart_dates']),
                type: 'datetime',
                labels: { format: 'dd MMM' }
            },
            colors: ['#0ab39c', '#f06548'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
            },
            grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
            tooltip: { x: { format: 'dd MMM yyyy' } }
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
                height: 320,
                fontFamily: 'inherit',
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '16px',
                                fontFamily: 'inherit',
                                fontWeight: 600,
                                color: '#373d3f',
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
            },
            colors: ['#556ee6', '#f1b44c', '#34c38f', '#f46a6a', '#50a5f1'],
            tooltip: {
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