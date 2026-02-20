<x-app-layout title="Top Dispatched Report">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Top Dispatched Report',
            'items' => [
                ['name' => 'Reports', 'url' => route('warehouse.reports.index')]
            ]
        ])

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <form action="{{ route('warehouse.reports.top-dispatched') }}" method="GET" class="d-flex align-items-center gap-3">
                    <label class="form-label fw-bold mb-0">Timeframe:</label>
                    <select name="days" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last Year</option>
                    </select>
                </form>
                <div>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="mdi mdi-printer me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Weight-Based Items --}}
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title text-primary mb-0 fw-bold">
                            <i class="mdi mdi-scale me-2"></i> Top Weight-Based Dispatches
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Product</th>
                                        <th class="text-end">Total Dispatched</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($weightBased as $index => $item)
                                        <tr>
                                            <td><span class="badge bg-primary rounded-pill">{{ $index + 1 }}</span></td>
                                            <td class="fw-semibold">{{ $item->product->product_name }}</td>
                                            <td class="text-end fw-bold text-success">{{ number_format($item->total_dispatched, 2) }}</td>
                                            <td>{{ $item->product->unit }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No data available for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Unit-Based Items --}}
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="card-title text-info mb-0 fw-bold">
                            <i class="mdi mdi-package-variant-closed me-2"></i> Top Unit-Based Dispatches
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Product</th>
                                        <th class="text-end">Total Dispatched</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unitBased as $index => $item)
                                        <tr>
                                            <td><span class="badge bg-info rounded-pill">{{ $index + 1 }}</span></td>
                                            <td class="fw-semibold">{{ $item->product->product_name }}</td>
                                            <td class="text-end fw-bold text-success">{{ number_format($item->total_dispatched, 0) }}</td>
                                            <td>{{ $item->product->unit }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No data available for this period.</td>
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
</x-app-layout>
