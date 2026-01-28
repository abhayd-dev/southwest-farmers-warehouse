<x-app-layout title="Stock & Inventory Dashboard">
    <div class="container-fluid">

        {{-- HEADER WITH BOTH BUTTONS --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-clipboard-list text-primary"></i> Stock & Inventory Dashboard
                </h4>
                <small class="text-muted">Real-time stock levels across warehouse</small>
            </div>
            <div class="d-flex gap-2">
                {{-- Adjustment Button --}}
                <a href="{{ route('warehouse.stocks.adjust') }}" class="btn btn-warning text-dark">
                    <i class="mdi mdi-scale-balance me-1"></i> Stock Adjustment
                </a>

                {{-- Purchase Button --}}
                <a href="{{ route('warehouse.stocks.create') }}" class="btn btn-success">
                    <i class="mdi mdi-plus-box me-1"></i> Stock In (Purchase)
                </a>
            </div>
        </div>

        {{-- FILTERS (Same as before) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Search Product</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, SKU, Barcode..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Filter By</label>
                        <select name="filter" class="form-select">
                            <option value="">All Stock</option>
                            <option value="low_stock" {{ request('filter') == 'low_stock' ? 'selected' : '' }}>⚠️ Low
                                Stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-filter"></i>
                            Apply</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- STOCK TABLE (Same as before) --}}
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Product Info</th>
                            <th>Category</th>
                            <th class="text-center">Current Quantity</th>
                            <th class="text-end px-4">Value</th>
                            <th class="text-end px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-light rounded p-1 border">
                                            {{-- Product Icon --}}
                                            <img src="{{ $stock->product->icon ? Storage::url( $stock->product->icon) : 'https://placehold.co/40?text=IMG' }}"
                                                class="rounded bg-light border object-fit-cover" width="40"
                                                height="40">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $stock->product->product_name }}</div>
                                            <div class="small text-muted">
                                                SKU: {{ $stock->product->sku ?? 'N/A' }} |
                                                Unit: <span
                                                    class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $stock->product->unit }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $stock->product->category->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $isLow = $stock->quantity <= $stock->min_stock_level;
                                        $color = $stock->quantity == 0 ? 'danger' : ($isLow ? 'warning' : 'success');
                                    @endphp
                                    <h5 class="mb-0 text-{{ $color }} fw-bold">
                                        {{ number_format($stock->quantity, 2) }}</h5>
                                    @if ($isLow)
                                        <small class="text-danger fw-bold"><i class="mdi mdi-alert-circle"></i> Low
                                            Stock</small>
                                    @endif
                                </td>
                                <td class="text-end px-4">
                                    <div class="fw-semibold">
                                        ${{ number_format($stock->quantity * ($stock->product->cost_price ?? 0), 2) }}
                                    </div>
                                </td>
                                <td class="text-end px-4">
                                    <a href="{{ route('warehouse.stocks.history', $stock->product_id) }}"
                                        class="btn btn-sm btn-outline-info" title="View Full History">
                                        <i class="mdi mdi-history"></i> History
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="mdi mdi-package-variant-closed text-muted opacity-25"
                                            style="font-size: 4rem;"></i>
                                        <p class="text-muted mt-2">No stock data found.</p>
                                        <a href="{{ route('warehouse.stocks.create') }}"
                                            class="btn btn-primary btn-sm">Add First Stock</a>
                                    </div>
                                    
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-3">{{ $stocks->withQueryString()->links() }}</div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
                const historyContent = document.getElementById('historyContent');

                document.querySelectorAll('.history-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const productId = this.dataset.id;
                        historyModal.show();
                        historyContent.innerHTML =
                            '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

                        fetch("{{ url('warehouse/stocks') }}/" + productId + "/history")
                            .then(response => response.text())
                            .then(html => {
                                historyContent.innerHTML = html;
                            })
                            .catch(err => {
                                historyContent.innerHTML =
                                    '<div class="text-center py-4 text-danger">Failed to load history.</div>';
                            });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
