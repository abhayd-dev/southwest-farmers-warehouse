<x-app-layout title="Restock Planning">
    <div class="container-fluid">
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Restock Planning & Alerts',
            'items' => [
                ['name' => 'Stock Control', 'url' => route('warehouse.stock-control.overview')],
                ['name' => 'Restock Planning', 'url' => '#']
            ]
        ])

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                <h5 class="card-title text-primary mb-0 fw-bold">
                    <i class="mdi mdi-clipboard-text-play me-2"></i> Inventory Restock Forecast
                </h5>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="mdi mdi-printer me-1"></i> Print Forecast
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Product</th>
                                <th>Category</th>
                                <th class="text-end">Qty on Hand</th>
                                <th class="text-end">In Transit</th>
                                <th class="text-end">Lead Time</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end fw-bold text-primary">Rec. Order Qty</th>
                                <th>Suggested Reorder Date</th>
                                <th class="text-center pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($planningData as $item)
                                <tr>
                                    <td class="ps-4 fw-semibold">
                                        {{ $item->product->product_name }}
                                        <div class="small text-muted">{{ $item->product->sku }}</div>
                                    </td>
                                    <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                                    
                                    <td class="text-end">{{ number_format($item->qty_in_hand, 2) }}</td>
                                    <td class="text-end text-info">{{ number_format($item->in_transit, 2) }}</td>
                                    
                                    <td class="text-end">{{ $item->lead_time }} Days</td>
                                    <td class="text-end">${{ number_format($item->cost, 2) }}</td>
                                    
                                    <td class="text-end fw-bold text-primary">
                                        {{ number_format($item->recommended_order, 2) }}
                                    </td>
                                    
                                    <td>
                                        @if($item->action_required)
                                            <span class="badge bg-danger">Order ASAP</span>
                                        @else
                                            <span class="text-muted">{{ $item->suggested_date }}</span>
                                        @endif
                                    </td>

                                    <td class="text-center pe-4">
                                        @if($item->is_fast_moving)
                                            <span class="badge bg-warning text-dark"><i class="mdi mdi-lightning-bolt"></i> Fast Moving</span>
                                        @endif
                                        @if($item->action_required)
                                            <span class="badge bg-danger"><i class="mdi mdi-alert"></i> Low Stock</span>
                                        @endif
                                        @if(!$item->is_fast_moving && !$item->action_required)
                                            <span class="badge bg-success"><i class="mdi mdi-check"></i> Healthy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-package-variant display-4 mb-3 d-block"></i>
                                        No active products found for restock analysis.
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
