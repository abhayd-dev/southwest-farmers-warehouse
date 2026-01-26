<x-app-layout title="Product Analytics: {{ $product->product_name }}">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stock-control.valuation') }}">Valuation</a></li>
                        <li class="breadcrumb-item active">{{ $product->product_name }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-chart-box-outline text-info me-2"></i> {{ $product->product_name }}
                    <small class="text-muted fs-6 ms-2">({{ $product->sku }})</small>
                </h4>
            </div>
            <a href="{{ route('warehouse.stock-control.valuation') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                    <div class="card-body text-center p-4">
                        <h6 class="text-white-50 mb-1">Total System Value</h6>
                        <h3 class="fw-bold">$ {{ number_format($totalValue, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <h6 class="text-muted mb-1">Warehouse Stock</h6>
                        <h3 class="fw-bold text-dark">{{ $warehouseQty }} <small class="fs-6 text-muted">{{ $product->unit }}</small></h3>
                        <small class="text-success">Value: $ {{ number_format($warehouseValue, 2) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <h6 class="text-muted mb-1">Total Store Stock</h6>
                        <h3 class="fw-bold text-dark">{{ $storesQty }} <small class="fs-6 text-muted">{{ $product->unit }}</small></h3>
                        <small class="text-info">Value: $ {{ number_format($storesValue, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Store Distribution</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Store</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end pe-4">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($storeDistribution as $sd)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $sd->store_name }}</td>
                                            <td class="text-center">{{ $sd->quantity }}</td>
                                            <td class="text-end pe-4">$ {{ number_format($sd->value, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No stock in stores.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Warehouse Batches (FIFO)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Batch No</th>
                                        <th>Expiry</th>
                                        <th class="text-end pe-4">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $batch)
                                        <tr>
                                            <td class="ps-4 font-monospace">{{ $batch->batch_number }}</td>
                                            <td>
                                                {{ $batch->expiry_date ? $batch->expiry_date->format('d M Y') : 'N/A' }}
                                                @if($batch->expiry_date && $batch->expiry_date < now())
                                                    <span class="badge bg-danger ms-1">Expired</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4 fw-bold">{{ $batch->quantity }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No active batches in warehouse.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Recent Transactions (Last 20)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Type</th>
                                    <th>Ref ID</th>
                                    <th>Location/Store</th>
                                    <th>Change</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $txn)
                                    <tr>
                                        <td class="ps-4 text-muted small">{{ $txn->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $txn->quantity_change > 0 ? 'success' : 'warning' }} text-uppercase">
                                                {{ $txn->type }}
                                            </span>
                                        </td>
                                        <td class="font-monospace small">{{ $txn->reference_id ?? '-' }}</td>
                                        <td>{{ $txn->store->store_name ?? 'Warehouse' }}</td>
                                        <td class="fw-bold {{ $txn->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $txn->quantity_change > 0 ? '+' : '' }}{{ $txn->quantity_change }}
                                        </td>
                                        <td class="small">{{ $txn->user->name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No recent history.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>