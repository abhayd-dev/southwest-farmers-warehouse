<x-app-layout title="Stock & Inventory Dashboard">
    <div class="container-fluid p-3 p-md-4">

        {{-- HEADER SECTION --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-clipboard-list text-primary"></i> Stock & Inventory
                </h4>
                <small class="text-muted">Real-time stock levels across warehouse</small>
            </div>
            
            <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-end">
                {{-- Adjustment Button --}}
                <a href="{{ route('warehouse.stocks.adjust') }}" class="btn btn-warning text-dark flex-fill flex-md-grow-0">
                    <i class="mdi mdi-scale-balance me-1"></i> Stock Adjustment
                </a>

                {{-- Purchase Button --}}
                <a href="{{ route('warehouse.stocks.create') }}" class="btn btn-success flex-fill flex-md-grow-0">
                    <i class="mdi mdi-plus-box me-1"></i> Stock In (Purchase)
                </a>
            </div>
        </div>

        {{-- FILTERS SECTION --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.stocks.index') }}" class="row g-3 align-items-end">
                    
                    {{-- Search --}}
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">Search Product</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Name, SKU, Barcode..." value="{{ request('search') }}">
                        </div>
                    </div>

                    {{-- Category Filter --}}
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label small fw-bold text-muted">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Action Buttons --}}
                    <div class="col-12 col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1 text-nowrap">
                                <i class="mdi mdi-filter me-1"></i> Apply
                            </button>
                            <a href="{{ route('warehouse.stocks.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                                <i class="mdi mdi-refresh"></i>
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        {{-- STOCK TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Product Info</th>
                                <th class="py-3">Category</th>
                                <th class="py-3">Bin Loc</th>
                                <th class="text-center py-3">Current Quantity</th>
                                <th class="text-end px-4 py-3">Value</th>
                                <th class="text-end px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $stock)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light rounded p-1 border flex-shrink-0">
                                                <img src="{{ $stock->product->icon ? Storage::url($stock->product->icon) : 'https://placehold.co/40?text=IMG' }}"
                                                    class="rounded bg-light border object-fit-cover" width="40" height="40">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-wrap" style="max-width: 250px;">{{ $stock->product->product_name }}</div>
                                                <div class="small text-muted">
                                                    SKU: {{ $stock->product->sku ?? 'N/A' }} 
                                                    <span class="mx-1">|</span>
                                                    Unit: <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $stock->product->unit }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $stock->product->category->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        @if($stock->bin_location)
                                            <span class="badge bg-warning border text-dark">{{ $stock->bin_location }}</span>
                                        @else
                                            <span class="text-muted small fst-italic">--</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3">
                                        @php
                                            $isLow = $stock->quantity <= $stock->min_stock_level;
                                            $color = $stock->quantity == 0 ? 'danger' : ($isLow ? 'warning' : 'success');
                                        @endphp
                                        <h5 class="mb-0 text-{{ $color }} fw-bold">
                                            {{ number_format($stock->quantity, 2) }}
                                        </h5>
                                        @if ($isLow)
                                            <small class="text-danger fw-bold d-block mt-1">
                                                <i class="mdi mdi-alert-circle"></i> Low Stock
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end px-4 py-3">
                                        <div class="fw-semibold text-dark">
                                            ${{ number_format($stock->quantity * ($stock->product->cost_price ?? 0), 2) }}
                                        </div>
                                    </td>
                                    <td class="text-end px-4 py-3">
                                        <a href="{{ route('warehouse.stocks.history', $stock->product_id) }}"
                                            class="btn btn-sm btn-outline-info" title="View Full History">
                                            <i class="mdi mdi-history me-1"></i> History
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="mdi mdi-package-variant-closed text-muted opacity-25" style="font-size: 4rem;"></i>
                                            <p class="text-muted mt-2 fw-medium">No stock data found.</p>
                                            <a href="{{ route('warehouse.stocks.create') }}" class="btn btn-primary btn-sm px-4">
                                                <i class="mdi mdi-plus me-1"></i> Add First Stock
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($stocks->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $stocks->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- SCRIPTS --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Any specific script initialization can go here if needed in future
            });
        </script>
    @endpush
</x-app-layout>