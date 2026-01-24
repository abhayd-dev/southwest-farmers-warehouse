<x-app-layout title="Store Analytics: {{ $store->store_name }}">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stock-control.valuation') }}">Valuation</a></li>
                        <li class="breadcrumb-item active">{{ $store->store_name }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-store-search text-primary me-2"></i> {{ $store->store_name }}
                </h4>
            </div>
            <a href="{{ route('warehouse.stock-control.valuation') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Total Stock Value</h6>
                        <h2 class="text-primary fw-bold mb-0">₹ {{ number_format($storeValue, 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small fw-bold">Total Items in Stock</h6>
                        <h2 class="text-success fw-bold mb-0">{{ number_format($storeQty) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Top 10 High Value Products</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th>SKU</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end pe-4">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProducts as $p)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $p->product_name }}</td>
                                            <td>{{ $p->sku }}</td>
                                            <td class="text-center">{{ $p->quantity }}</td>
                                            <td class="text-end pe-4 fw-bold text-success">₹ {{ number_format($p->value, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No stock data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Activity Trend (30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <div id="trendChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // Prepare Data for Chart
        const dates = @json($trend->pluck('date'));
        const counts = @json($trend->pluck('transactions'));

        var options = {
            series: [{
                name: 'Transactions',
                data: counts
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: { borderRadius: 4, columnWidth: '50%' }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: dates,
                labels: { show: false } // Hide dense labels
            },
            colors: ['#6f42c1'],
            title: {
                text: 'Stock Movement Count',
                align: 'center',
                style: { fontSize: '14px', color: '#666' }
            }
        };

        var chart = new ApexCharts(document.querySelector("#trendChart"), options);
        chart.render();
    </script>
    @endpush
</x-app-layout>