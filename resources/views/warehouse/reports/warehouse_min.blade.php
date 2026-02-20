<x-app-layout title="Warehouse Min Report">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Warehouse Safety Minimum Report',
            'items' => [
                ['name' => 'Reports', 'url' => route('warehouse.reports.index')]
            ]
        ])

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title text-danger mb-0 fw-bold">
                    <i class="mdi mdi-alert-circle-outline me-2"></i> Items Below Minimum Safety Levels
                </h5>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="mdi mdi-printer me-1"></i> Print Alert
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">SKU</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Minimum Level</th>
                                <th class="text-end pe-4">Deficit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockItems as $item)
                                @php
                                    $currentQty = $item->stock ? $item->stock->quantity : 0;
                                    // Make sure min_level exists, since product_min_max_levels was joined
                                    $minLevel = $item->min_level ?? 0;
                                    $deficit = $minLevel - $currentQty;
                                @endphp
                                <tr>
                                    <td class="ps-4"><span class="badge bg-secondary">{{ $item->sku }}</span></td>
                                    <td class="fw-semibold">{{ $item->product_name }}</td>
                                    <td>{{ $item->category->name ?? 'Uncategorized' }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($currentQty, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($minLevel, 2) }}</td>
                                    <td class="text-end pe-4 fw-bold text-danger">-{{ number_format($deficit, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-check-circle-outline display-4 text-success mb-3 d-block"></i>
                                        All items are currently above their safety minimum levels.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
