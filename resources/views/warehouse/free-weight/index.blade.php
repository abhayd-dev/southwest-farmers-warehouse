<x-app-layout title="Free Weight System">
    <div class="container-fluid p-3 p-md-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-scale text-primary"></i> Free Weight System
                </h4>
                <small class="text-muted">Manage bulk weight products and convert them into sellable packages</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('warehouse.free-weight.history') }}" class="btn btn-outline-secondary">
                    <i class="mdi mdi-history me-1"></i> Event History
                </a>
                <a href="{{ route('warehouse.free-weight.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-1"></i> Register Bulk Product
                </a>
            </div>
        </div>

        {{-- BULK PRODUCTS GRID --}}
        @forelse($bulkProducts as $bulk)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <i class="mdi mdi-package-variant text-warning me-2"></i>
                            {{ $bulk->product->product_name ?? 'N/A' }}
                        </h5>
                        <small class="text-muted">Bulk Stock</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end">
                            <div class="fs-4 fw-bold {{ $bulk->bulk_weight < 100 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($bulk->bulk_weight, 2) }} {{ $bulk->unit }}
                            </div>
                            <small class="text-muted">Available Bulk Weight</small>
                        </div>
                        <div class="action-btns">
                            <a href="{{ route('warehouse.free-weight.package.create', $bulk->id) }}" class="btn btn-sm btn-outline-info" title="Add Package Size">
                                <i class="mdi mdi-plus-box"></i>
                            </a>
                            <a href="{{ route('warehouse.free-weight.event.create', $bulk->id) }}" class="btn btn-sm btn-outline-success" style="color: #10b981; border-color: #10b981;" title="Start Packaging">
                                <i class="mdi mdi-play"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Package Definitions --}}
                @if($bulk->packages->isNotEmpty())
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-2">Package Name</th>
                                    <th class="py-2">Size</th>
                                    <th class="py-2">SKU</th>
                                    <th class="py-2">Target Product</th>
                                    <th class="text-end px-4 py-2">Total Produced</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bulk->packages as $pkg)
                                    <tr>
                                        <td class="px-4 py-2 fw-semibold">{{ $pkg->package_name }}</td>
                                        <td class="py-2">{{ $pkg->package_size }} {{ $pkg->unit }}</td>
                                        <td class="py-2"><code>{{ $pkg->sku }}</code></td>
                                        <td class="py-2">
                                            @if($pkg->targetProduct)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                    {{ $pkg->targetProduct->product_name }}
                                                </span>
                                            @else
                                                <span class="text-muted small fst-italic">No linked product</span>
                                            @endif
                                        </td>
                                        <td class="text-end px-4 py-2 fw-bold">{{ number_format($pkg->quantity_created) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="card-body text-center text-muted py-4">
                        <i class="mdi mdi-package-variant-closed-remove fs-2 opacity-25"></i>
                        <p class="mt-2 mb-0">No package sizes defined yet.</p>
                        <a href="{{ route('warehouse.free-weight.package.create', $bulk->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                            Add Package Size
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="mdi mdi-scale-off text-muted opacity-25" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-2 fw-medium">No bulk products registered yet.</p>
                    <a href="{{ route('warehouse.free-weight.create') }}" class="btn btn-primary btn-sm px-4">
                        <i class="mdi mdi-plus me-1"></i> Register First Bulk Product
                    </a>
                </div>
            </div>
        @endforelse

        {{-- RECENT EVENTS --}}
        @if($recentEvents->isNotEmpty())
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="mdi mdi-history text-info me-2"></i> Recent Packaging Events</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="py-2">Bulk Product</th>
                                <th class="py-2">Package</th>
                                <th class="py-2">Bulk Used</th>
                                <th class="py-2">Packages Created</th>
                                <th class="py-2">Employee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEvents as $event)
                                <tr>
                                    <td class="px-4 py-2 text-muted small">{{ $event->event_date->format('d M Y, H:i') }}</td>
                                    <td class="py-2 fw-semibold">{{ $event->freeWeightProduct->product->product_name ?? 'N/A' }}</td>
                                    <td class="py-2">{{ $event->package->package_name ?? 'N/A' }}</td>
                                    <td class="py-2 text-danger fw-bold">-{{ number_format($event->bulk_weight_reduced, 2) }} lbs</td>
                                    <td class="py-2 text-success fw-bold">+{{ number_format($event->packages_created) }} pcs</td>
                                    <td class="py-2">{{ $event->employee->name ?? 'System' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
