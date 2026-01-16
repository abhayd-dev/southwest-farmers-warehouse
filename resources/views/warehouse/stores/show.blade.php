<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stores.index') }}">Stores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $store->store_code }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">{{ $store->store_name }} <span class="badge bg-success fs-6 align-middle ms-2">Active</span></h4>
                <p class="text-muted mb-0"><i class="mdi mdi-map-marker me-1"></i> {{ $store->address }}, {{ $store->city }}</p>
            </div>
            <div>
                <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-primary shadow-sm">
                    <i class="mdi mdi-pencil me-1"></i> Edit Details
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Inventory Value</h6>
                        <h3 class="mb-0 fw-bold">₹{{ number_format($analytics['inventory_value']) }}</h3>
                        <small class="text-success"><i class="mdi mdi-trending-up me-1"></i>Current Stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Total Products</h6>
                        <h3 class="mb-0 fw-bold">{{ $analytics['inventory_items'] }}</h3>
                        <small class="text-muted">Unique SKUs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2">Low Stock Alerts</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $analytics['low_stock_count'] }}</h3>
                        <small class="text-danger fw-bold">Action Needed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-light">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar-md bg-white rounded-circle shadow-sm d-flex justify-content-center align-items-center me-3 text-primary fs-4">
                            {{ substr($store->manager->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $store->manager->name ?? 'No Manager' }}</h6>
                            <small class="d-block text-muted">{{ $store->manager->email ?? '' }}</small>
                            <small class="d-block text-muted">{{ $store->manager->phone ?? '' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4" style="min-height: 400px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Sales & Stock Analytics</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <div class="text-center text-muted">
                            <i class="mdi mdi-chart-bar fs-1 opacity-25"></i>
                            <p class="mt-2">Analytics Chart will load here (Future Update)</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Location Details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Code</span>
                                <span class="fw-bold">{{ $store->store_code }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">City</span>
                                <span class="fw-bold">{{ $store->city }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Pincode</span>
                                <span class="fw-bold">{{ $store->pincode }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Created</span>
                                <span class="fw-bold">{{ $store->created_at->format('d M, Y') }}</span>
                            </li>
                        </ul>
                        @if($store->latitude)
                        <div class="mt-3 bg-light rounded p-3 text-center">
                            <small class="text-muted d-block mb-1">Coordinates</small>
                            <code>{{ $store->latitude }}, {{ $store->longitude }}</code>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>