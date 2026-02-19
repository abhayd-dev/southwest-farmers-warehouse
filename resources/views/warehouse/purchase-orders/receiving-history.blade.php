<x-app-layout title="Receiving History — {{ $purchaseOrder->po_number }}">
    <div class="container-fluid p-3 p-md-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('warehouse.purchase-orders.show', $purchaseOrder->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-0">Receiving History</h4>
                    <small class="text-muted">PO #{{ $purchaseOrder->po_number }} &bull; {{ $purchaseOrder->vendor->name }}</small>
                </div>
            </div>
            <span class="badge bg-{{ $purchaseOrder->status === 'completed' ? 'success' : ($purchaseOrder->status === 'partial' ? 'warning' : 'secondary') }} fs-6 px-3 py-2">
                {{ strtoupper($purchaseOrder->status) }}
            </span>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-primary">{{ $purchaseOrder->items->sum('requested_quantity') }}</div>
                    <div class="small text-muted">Total Ordered</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-success">{{ $purchaseOrder->items->sum('received_quantity') }}</div>
                    <div class="small text-muted">Total Received</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-warning">{{ $purchaseOrder->items->sum('requested_quantity') - $purchaseOrder->items->sum('received_quantity') }}</div>
                    <div class="small text-muted">Still Pending</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="fw-bold fs-3 text-info">{{ $batches->count() }}</div>
                    <div class="small text-muted">Batches Created</div>
                </div>
            </div>
        </div>

        {{-- BATCHES TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="mdi mdi-package-variant me-2"></i>Received Batches</h6>
            </div>
            <div class="card-body p-0">
                @if($batches->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="mdi mdi-package-variant-closed-remove fs-1 opacity-25 d-block mb-2"></i>
                        No batches have been received for this PO yet.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small text-muted">
                                <tr>
                                    <th class="px-4 py-3">Batch Number</th>
                                    <th class="py-3">Product</th>
                                    <th class="py-3 text-center">Qty Received</th>
                                    <th class="py-3">Mfg Date</th>
                                    <th class="py-3">Expiry Date</th>
                                    <th class="py-3">Cost Price</th>
                                    <th class="py-3 text-center">Batch Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    @php
                                        $status = $batch->getStatus();
                                        $statusColors = [
                                            'expired'  => 'danger',
                                            'critical' => 'danger',
                                            'urgent'   => 'warning',
                                            'warning'  => 'info',
                                            'normal'   => 'success',
                                        ];
                                        $color = $statusColors[$status] ?? 'secondary';
                                        $daysLeft = $batch->getDaysToExpiry();
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 fw-semibold font-monospace">{{ $batch->batch_number }}</td>
                                        <td class="py-3">
                                            <div class="fw-semibold">{{ $batch->product->product_name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $batch->product->sku ?? '' }}</small>
                                        </td>
                                        <td class="py-3 text-center fw-bold">{{ number_format($batch->quantity, 0) }}</td>
                                        <td class="py-3">{{ $batch->manufacturing_date ? $batch->manufacturing_date->format('d M Y') : '—' }}</td>
                                        <td class="py-3">
                                            @if($batch->expiry_date)
                                                {{ $batch->expiry_date->format('d M Y') }}
                                                @if($daysLeft !== null && $daysLeft > 0)
                                                    <br><small class="text-muted">{{ $daysLeft }} days left</small>
                                                @elseif($daysLeft !== null && $daysLeft <= 0)
                                                    <br><small class="text-danger">Expired</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="py-3">${{ number_format($batch->cost_price, 2) }}</td>
                                        <td class="py-3 text-center">
                                            {!! $batch->getStatusBadge() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
