<x-app-layout title="Product Analytics: {{ $product->product_name }}">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a
                                href="{{ route('warehouse.stock-control.valuation') }}">Valuation</a></li>
                        <li class="breadcrumb-item active">{{ $product->product_name }}</li>
                    </ol>
                </nav>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-chart-box-outline text-info me-2"></i> {{ $product->product_name }}
                    <small class="text-muted fs-6 ms-2">({{ $product->upc }})</small>
                </h4>
            </div>
            <a href="{{ route('warehouse.stock-control.valuation') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Warehouse Stock</h6>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($warehouseQty, 0) }} <small class="fs-6 text-muted">{{ $product->unit ?? 'Units' }}</small></h3>
                                <small class="text-success fw-semibold">Value: $ {{ number_format($warehouseValue, 2) }}</small>
                            </div>
                            <div class="avatar-md rounded bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-warehouse text-info fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Stores Distribution Stock</h6>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($storesQty, 0) }} <small class="fs-6 text-muted">{{ $product->unit ?? 'Units' }}</small></h3>
                                <small class="text-success fw-semibold">Value: $ {{ number_format($storesValue, 2) }}</small>
                            </div>
                            <div class="avatar-md rounded bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-store text-success fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Activity Trend (30 Days)</h6>
                                <h3 class="fw-bold text-dark mb-0">{{ number_format($thirtyDaysActivity ?? 0, 0) }} <small class="fs-6 text-muted">Moved</small></h3>
                                <small class="text-warning fw-semibold"><i class="mdi mdi-chart-line me-1"></i>30 Days Stock Activity</small>
                            </div>
                            <div class="avatar-md rounded bg-warning bg-opacity-10 d-flex align-items-center justify-content-center">
                                <i class="mdi mdi-chart-timeline-variant text-warning fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-store-outline me-1"></i> Store Distribution</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Store</th>
                                        <th class="text-center">Qty</th>
                                        <th>Date</th>
                                        <th class="text-end pe-4">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($storeDistribution as $sd)
                                        <tr>
                                            <td class="ps-4 fw-bold">{{ $sd->store_name }}</td>
                                            <td class="text-center">{{ $sd->quantity }}</td>
                                            <td class="text-muted small">{{ $sd->last_activity ? \Carbon\Carbon::parse($sd->last_activity)->format('d M Y') : 'N/A' }}</td>
                                            <td class="text-end pe-4">$ {{ number_format($sd->value, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No stock in stores.</td>
                                        </tr>
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
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-package-variant-closed me-1"></i> Warehouse Batches (PO Orders)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">Batch No (Order No)</th>
                                        <th>Expiry</th>
                                        <th class="text-center">Total Qty</th>
                                        <th class="text-end">Unit Cost</th>
                                        <th class="text-end pe-4">Remaining Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $batch)
                                        <tr>
                                            <td class="ps-4 font-monospace">{{ $batch->batch_number }}</td>
                                            <td>
                                                {{ $batch->expiry_date ? $batch->expiry_date->format('d M Y') : 'N/A' }}
                                                @if ($batch->expiry_date && $batch->expiry_date < now())
                                                    <span class="badge bg-danger ms-1">Expired</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-muted">{{ $batch->initial_quantity ?? $batch->quantity }}</td>
                                            <td class="text-end fw-bold text-success">$ {{ number_format($batch->cost_price ?? $product->cost_price ?? 0, 2) }}</td>
                                            <td class="text-end pe-4 fw-bold">{{ $batch->quantity }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No active batches in warehouse.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-history me-1"></i> Recent Transactions</h5>
                        <form method="GET" action="{{ route('warehouse.stock-control.valuation.product', $product->id) }}" class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">From</span>
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">To</span>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            @if(request('from_date') || request('to_date'))
                                <a href="{{ route('warehouse.stock-control.valuation.product', $product->id) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            @endif
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Type (Ordered / Transfer)</th>
                                    <th>Store PO / Ref No</th>
                                    <th>Location Change</th>
                                    <th class="text-center">Qty Change</th>
                                    <th class="text-center">Balance</th>
                                    <th class="text-end">PO Unit Cost</th>
                                    <th class="text-end">Total Value</th>
                                    <th class="pe-4">User</th>
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
                                        <td class="text-center fw-bold {{ $txn->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $txn->quantity_change > 0 ? '+' : '' }}{{ $txn->quantity_change }}
                                        </td>
                                        <td class="text-center font-monospace fw-bold text-dark">{{ number_format($txn->calculated_running_balance ?? $txn->running_balance ?? 0, 2) }}</td>
                                        <td class="text-end fw-bold text-dark">$ {{ number_format($txn->unit_cost ?? $product->cost_price ?? 0, 2) }}</td>
                                        <td class="text-end fw-bold text-success">$ {{ number_format($txn->total_value ?? 0, 2) }}</td>
                                        <td class="pe-4 small">{{ $txn->user->name ?? 'System' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No recent history.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
