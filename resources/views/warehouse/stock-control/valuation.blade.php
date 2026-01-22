<x-app-layout title="Stock Valuation">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Stock Valuation'])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="mdi mdi-cash-multiple text-success me-2"></i> Stock Valuation
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success">
                <i class="mdi mdi-refresh me-1"></i> Refresh
            </button>
            <button class="btn btn-outline-primary">
                <i class="mdi mdi-download me-1"></i> Export Report
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg text-center h-100">
                <div class="card-body bg-gradient-primary text-danger p-4">
                    <h6 class="text-white-75 mb-2">Warehouse Value</h6>
                    <h3 class="fw-bold">₹ {{ number_format($warehouseValue, 2) }}</h3>
                    <small class="text-white-75">Current stock at cost</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg text-center h-100">
                <div class="card-body bg-gradient-success text-success p-4">
                    <h6 class="text-white-75 mb-2">All Stores Value</h6>
                    <h3 class="fw-bold">₹ {{ number_format($storesValue, 2) }}</h3>
                    <small class="text-white-75">Distributed stock value</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-lg text-center h-100">
                <div class="card-body bg-gradient-info text-warning p-4">
                    <h6 class="text-white-75 mb-2">Total Valuation</h6>
                    <h3 class="fw-bold">₹ {{ number_format($totalValue, 2) }}</h3>
                    <small class="text-white-75">Warehouse + Stores</small>
                </div>git pull origin develop

            </div>
        </div>
    </div>

    <!-- Trend Chart -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-light">
            <h5 class="mb-0">30-Day Valuation Trend (Approximate)</h5>
        </div>
        <div class="card-body">
            @if(empty($valueTrend) || count($valueTrend) === 0)
                <div class="alert alert-info text-center py-5">
                    <i class="mdi mdi-information-outline fs-1 d-block mb-3"></i>
                    No transaction data in last 30 days
                </div>
            @else
                <div id="valuationChart" style="height: 380px;"></div>
            @endif
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Top 15 Products by Valuation</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Warehouse Qty</th>
                            <th>Warehouse Value</th>
                            <th>Stores Qty</th>
                            <th>Stores Value</th>
                            <th class="text-end">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $p->product_name }}</td>
                                <td class="text-center">{{ number_format($p->warehouse_qty, 0) }}</td>
                                <td>₹ {{ number_format($p->warehouse_value, 2) }}</td>
                                <td class="text-center">{{ number_format($p->stores_qty, 0) }}</td>
                                <td>₹ {{ number_format($p->stores_value, 2) }}</td>
                                <td class="text-end fw-bold text-success">₹ {{ number_format($p->total_value, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-information-outline fs-3 d-block mb-2"></i>
                                    No products found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
@if(!empty($valueTrend) && count($valueTrend) > 0)
    var options = {
        series: [{
            name: 'Total Value',
            data: @json($valueTrend)
        }],
        chart: {
            height: 380,
            type: 'area',
            zoom: { enabled: false },
            toolbar: { show: true }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: {
            type: 'datetime',
            categories: @json($dates),
            labels: { format: 'dd MMM' }
        },
        yaxis: {
            title: { text: 'Value (₹)' },
            labels: { formatter: val => "₹ " + val.toLocaleString('en-IN') }
        },
        tooltip: {
            x: { format: 'dd MMM yyyy' },
            y: { formatter: val => "₹ " + val.toLocaleString('en-IN') }
        },
        colors: ['#198754'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] }
        }
    };

    var chart = new ApexCharts(document.querySelector("#valuationChart"), options);
    chart.render();
@endif
</script>
@endpush

</x-app-layout>