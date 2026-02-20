<x-app-layout title="Sales By Price Point Report">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Sales By Price Point',
            'items' => [
                ['name' => 'Reports', 'url' => route('warehouse.reports.index')]
            ]
        ])

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3 d-flex justify-content-between align-items-center">
                <form action="{{ route('warehouse.reports.sales-by-price-point') }}" method="GET" class="d-flex align-items-center gap-3">
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="card-title text-primary mb-0 fw-bold">
                    <i class="mdi mdi-tags me-2"></i> Unit Volumes Segmented By Sale Price
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="accordion" id="pricePointAccordion">
                    @forelse($groupedProducts as $productId => $group)
                        @php 
                            $firstItem = $group->first(); 
                            $product = $firstItem->product;
                            if(!$product) continue;
                        @endphp
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header" id="heading-{{ $productId }}">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $productId }}">
                                    <div class="d-flex justify-content-between w-100 pe-3">
                                        <span class="fw-bold text-dark">{{ $product->product_name }} <small class="text-muted ms-2">({{ $product->sku }})</small></span>
                                        <span class="badge bg-secondary rounded-pill">{{ $group->count() }} Price Points</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $productId }}" class="accordion-collapse collapse" data-bs-parent="#pricePointAccordion">
                                <div class="accordion-body p-0">
                                    <table class="table table-sm table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Sale Price Point</th>
                                                <th class="text-end">Total Units Sold At Price</th>
                                                <th class="text-end pe-4">Total Revenue Generated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group as $item)
                                                <tr>
                                                    <td class="ps-4 fw-bold text-success">${{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="text-end fw-semibold">{{ number_format($item->total_qty, 2) }} {{ $product->unit ?? 'units' }}</td>
                                                    <td class="text-end pe-4">${{ number_format($item->revenue, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th class="ps-4">Total Aggregate</th>
                                                <th class="text-end text-primary fw-bold">{{ number_format($group->sum('total_qty'), 2) }} {{ $product->unit ?? 'units' }}</th>
                                                <th class="text-end pe-4 text-primary fw-bold">${{ number_format($group->sum('revenue'), 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center text-muted">
                            <i class="mdi mdi-chart-box-outline display-4 d-block mb-3"></i>
                            No sales records found for this timeframe.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
