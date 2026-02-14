<x-app-layout title="Finance Dashboard">
    <div class="container-fluid">
        
        {{-- HEADER & FILTERS --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                    {{-- Title --}}
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-chart-line text-primary me-2"></i> Financial Overview
                        </h4>
                        <small class="text-muted">Procurement Costs vs Inventory Valuation</small>
                    </div>
                    
                    {{-- Filter Form --}}
                    <form method="GET" class="d-flex flex-column flex-md-row gap-2 bg-light p-2 rounded w-100 w-lg-auto">
                        <div class="d-flex gap-2 align-items-center flex-grow-1">
                            <input type="date" name="start_date" value="{{ $startDate }}" 
                                class="form-control form-control-sm border-0 bg-white shadow-sm" 
                                aria-label="Start Date">
                            <span class="align-self-center text-muted fw-bold">-</span>
                            <input type="date" name="end_date" value="{{ $endDate }}" 
                                class="form-control form-control-sm border-0 bg-white shadow-sm" 
                                aria-label="End Date">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-dark shadow-sm fw-bold px-3 flex-grow-1 flex-md-grow-0">
                                <i class="mdi mdi-filter me-1"></i> Apply
                            </button>
                            <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary shadow-sm" title="Reset Filters">
                                <i class="mdi mdi-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 1. KPI CARDS --}}
        <div class="row g-4 mb-4">
            {{-- Procurement --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                    <div class="card-body position-relative z-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-bold text-uppercase small mb-1">Total Procurement Cost</p>
                                <h2 class="mb-0 fw-bold text-danger">${{ number_format($totalProcurement, 2) }}</h2>
                                <small class="text-danger fw-bold d-flex align-items-center mt-1">
                                    <i class="mdi mdi-arrow-down me-1"></i> Outflow (Money Spent)
                                </small>
                            </div>
                            <div class="p-3 bg-danger bg-opacity-10 rounded-3">
                                <i class="mdi mdi-cart-arrow-down text-danger fs-2"></i>
                            </div>
                        </div>
                    </div>
                    {{-- Decorative Circle --}}
                    <div class="position-absolute bg-danger opacity-10 rounded-circle" style="width: 140px; height: 140px; top: -40px; right: -40px;"></div>
                </div>
            </div>

            {{-- Dispatch --}}
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                    <div class="card-body position-relative z-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-bold text-uppercase small mb-1">Dispatch Valuation</p>
                                <h2 class="mb-0 fw-bold text-info">${{ number_format($totalDispatchValue, 2) }}</h2>
                                <small class="text-info fw-bold d-flex align-items-center mt-1">
                                    <i class="mdi mdi-arrow-up me-1"></i> Inflow (Value to Stores)
                                </small>
                            </div>
                            <div class="p-3 bg-info bg-opacity-10 rounded-3">
                                <i class="mdi mdi-truck-fast text-info fs-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute bg-info opacity-10 rounded-circle" style="width: 140px; height: 140px; top: -40px; right: -40px;"></div>
                </div>
            </div>

            {{-- Net Inventory --}}
            <div class="col-12 col-md-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                    <div class="card-body position-relative z-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-bold text-uppercase small mb-1">Inventory Asset Value</p>
                                <h2 class="mb-0 fw-bold text-success">${{ number_format($currentInventoryValue, 2) }}</h2>
                                <small class="text-success fw-bold d-flex align-items-center mt-1">
                                    <i class="mdi mdi-bank me-1"></i> Current Assets
                                </small>
                            </div>
                            <div class="p-3 bg-success bg-opacity-10 rounded-3">
                                <i class="mdi mdi-safe text-success fs-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute bg-success opacity-10 rounded-circle" style="width: 140px; height: 140px; top: -40px; right: -40px;"></div>
                </div>
            </div>
        </div>

        {{-- 2. CHARTS SECTION --}}
        <div class="row g-4">
            
            {{-- LINE CHART: TRENDS --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark">Cash Flow Analysis (Daily Trend)</h6>
                        <small class="text-muted d-none d-sm-inline"><i class="mdi mdi-information-outline"></i> Daily comparison</small>
                    </div>
                    <div class="card-body">
                        {{-- WRAPPER DIV IS CRITICAL FOR FIXING HEIGHT ISSUE --}}
                        <div style="position: relative; height: 350px; width: 100%;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DOUGHNUT CHART: CATEGORY DISTRIBUTION --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-dark">Value by Category</h6>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        {{-- WRAPPER DIV --}}
                        <div style="position: relative; height: 280px; width: 100%;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                        <div class="mt-3 text-center small text-muted">
                            Top categories by inventory cost price
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    {{-- Chart.js from CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // 1. TREND CHART (Line/Area)
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        
        // Gradients for aesthetics
        let gradientProcurement = ctxTrend.createLinearGradient(0, 0, 0, 400);
        gradientProcurement.addColorStop(0, 'rgba(220, 53, 69, 0.2)'); // Red
        gradientProcurement.addColorStop(1, 'rgba(220, 53, 69, 0)');

        let gradientDispatch = ctxTrend.createLinearGradient(0, 0, 0, 400);
        gradientDispatch.addColorStop(0, 'rgba(13, 202, 240, 0.2)'); // Info/Blue
        gradientDispatch.addColorStop(1, 'rgba(13, 202, 240, 0)');

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Procurement Cost ($)',
                        data: {!! json_encode($procurementData) !!},
                        borderColor: '#dc3545',
                        backgroundColor: gradientProcurement,
                        borderWidth: 2,
                        tension: 0.4, // Smooth Curves
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Dispatch Value ($)',
                        data: {!! json_encode($dispatchData) !!},
                        borderColor: '#0dcaf0',
                        backgroundColor: gradientDispatch,
                        borderWidth: 2,
                        tension: 0.4, // Smooth Curves
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Fits to the parent div height (350px)
                plugins: {
                    legend: { 
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 13 },
                        bodyFont: { size: 13 },
                        padding: 10,
                        cornerRadius: 4
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f0f0f0', borderDash: [5, 5] },
                        ticks: { callback: function(value) { return '$' + value; } }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { maxTicksLimit: 10 }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });

        // 2. CATEGORY CHART (Doughnut)
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($pieLabels) !!},
                datasets: [{
                    data: {!! json_encode($pieData) !!},
                    backgroundColor: [
                        '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Fits to parent div (280px)
                plugins: {
                    legend: { 
                        position: 'right', 
                        labels: { boxWidth: 12, font: { size: 11 }, padding: 15 } 
                    }
                },
                cutout: '75%', // Thinner ring
                layout: {
                    padding: 10
                }
            }
        });
    </script>
    @endpush
</x-app-layout>