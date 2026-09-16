<x-app-layout title="Promotions">
    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-ticket-percent-outline text-primary"></i> Promotions
                        </h4>
                        <p class="text-muted small mb-0">Promotions across every store — filter by store or by a specific day below.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" class="row g-2">
                    <div class="col-12 col-sm-6 col-md-4">
                        <select name="store_id" class="form-select border-light shadow-sm">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->store_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <input type="date" name="day" class="form-control border-light shadow-sm"
                            value="{{ request('day') }}" placeholder="Active on day">
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                <i class="mdi mdi-filter me-1"></i> Apply Filter
                            </button>
                            @if(request()->anyFilled(['store_id', 'day']))
                                <a href="{{ route('warehouse.promotions.index') }}" class="btn btn-outline-danger shadow-sm">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase fw-bold">Store</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Promotion</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Applies To</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Type / Value</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Start</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">End</th>
                                <th class="px-4 py-3 small text-muted text-uppercase fw-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promotions as $promo)
                                <tr>
                                    <td class="px-4">{{ $promo->store->store_name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $promo->name }}</div>
                                        @if($promo->code)
                                            <small class="text-muted">Code: {{ $promo->code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($promo->product)
                                            {{ $promo->product->product_name }}
                                        @elseif($promo->category)
                                            {{ $promo->category->name }} (category)
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ ucfirst(str_replace('_', ' ', $promo->type)) }} —
                                        {{ $promo->type === 'percentage' ? $promo->value . '%' : '$' . number_format($promo->value, 2) }}
                                    </td>
                                    <td>{{ optional($promo->start_date)->format('d M Y') }}</td>
                                    <td>{{ optional($promo->end_date)->format('d M Y') }}</td>
                                    <td class="px-4 text-center">
                                        @if($promo->isValid())
                                            <span class="badge bg-success">Live</span>
                                        @elseif($promo->is_active)
                                            <span class="badge bg-secondary">Scheduled/Expired</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        No promotions found for this filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($promotions->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $promotions->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
