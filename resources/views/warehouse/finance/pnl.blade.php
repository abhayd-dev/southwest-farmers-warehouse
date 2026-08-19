@extends('layouts.app')

@section('title', 'Profit & Loss Report')

@section('content')
<div class="container-fluid">

    {{-- Header & Filters --}}
    <div class="row align-items-center justify-content-between mb-4">
        <div class="col-12 col-md-auto mb-3 mb-md-0">
            <h4 class="mb-0 fw-bold text-dark"><i class="mdi mdi-finance text-primary me-2"></i> Profit & Loss Statement</h4>
            <p class="text-muted mb-0 small">Analyze revenue, COGS, and gross profit over time.</p>
        </div>
        <div class="col-12 col-md-auto">
            <form action="{{ route('warehouse.finance.pnl') }}" method="GET" class="d-flex align-items-center gap-2">
                <select name="range" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="month" {{ $range == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="3_months" {{ $range == '3_months' ? 'selected' : '' }}>Last 3 Months</option>
                    <option value="year" {{ $range == 'year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $range == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
                
                @if($range == 'custom')
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate->format('Y-m-d') }}" required>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate->format('Y-m-d') }}" required>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                @endif
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-4 g-3">
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-4 text-center">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Total Revenue</p>
                    <h3 class="mb-0 fw-bold text-dark">${{ number_format($totalRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-4 text-center">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Total COGS</p>
                    <h3 class="mb-0 fw-bold text-danger">-${{ number_format($totalCogs, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-4 text-center">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Gross Profit</p>
                    <h3 class="mb-0 fw-bold {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        ${{ number_format($grossProfit, 2) }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-4 text-center">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Gross Margin</p>
                    <h3 class="mb-0 fw-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($margin, 1) }}%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Trend Chart --}}
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h6 class="mb-0 fw-bold text-dark">Revenue vs Profit Trend</h6>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div id="pnlTrendChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>

        {{-- Category Breakdown --}}
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h6 class="mb-0 fw-bold text-dark">Profit by Category</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-4 border-0 small text-muted">Category</th>
                                    <th class="border-0 small text-muted text-end">Revenue</th>
                                    <th class="border-0 small text-muted text-end">COGS</th>
                                    <th class="pe-4 border-0 small text-muted text-end">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categoryBreakdown as $catName => $data)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark small">{{ $catName }}</td>
                                    <td class="text-end text-success small">${{ number_format($data['revenue'], 2) }}</td>
                                    <td class="text-end text-danger small">${{ number_format($data['cogs'], 2) }}</td>
                                    <td class="pe-4 text-end fw-bold small {{ $data['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($data['profit'], 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No sales data found for this period.</td>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var labels = {!! json_encode($chartLabels) !!};
        var revenueData = {!! json_encode($revenueData) !!};
        var profitData = {!! json_encode($profitData) !!};

        var options = {
            series: [{
                name: 'Revenue',
                data: revenueData
            }, {
                name: 'Profit',
                data: profitData
            }],
            chart: {
                height: 350,
                type: 'area',
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false }
            },
            colors: ['#0d6efd', '#198754'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: labels,
                tooltip: { enabled: false }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "$" + value.toLocaleString();
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "$" + val.toLocaleString();
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        };

        if (document.querySelector("#pnlTrendChart")) {
            var chart = new ApexCharts(document.querySelector("#pnlTrendChart"), options);
            chart.render();
        }
    });
</script>
@endpush
