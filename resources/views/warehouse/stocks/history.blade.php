<x-app-layout title="Stock History - {{ $product->product_name }}">
    <div class="container-fluid">
        
        {{-- HEADER & BREADCRUMB --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stocks.index') }}">Inventory</a></li>
                        <li class="breadcrumb-item active">Transaction History</li>
                    </ol>
                </nav>
                <h4 class="fw-bold text-dark mb-0">
                    <i class="mdi mdi-history text-primary"></i> {{ $product->product_name }}
                </h4>
                <div class="small text-muted mt-1">
                    SKU: <span class="text-dark fw-bold">{{ $product->sku }}</span> | 
                    Current Stock: <span class="badge bg-success fs-6">{{ $product->stock->quantity ?? 0 }} {{ $product->unit }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('warehouse.stocks.index') }}" class="btn btn-outline-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>

        {{-- FILTERS CARD --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-muted"><i class="mdi mdi-filter-variant"></i> Filter Transactions</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    
                    {{-- Date Range --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    {{-- Transaction Type --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Purchase (In)</option>
                            <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Sale (Out)</option>
                            <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            <option value="damage" {{ request('type') == 'damage' ? 'selected' : '' }}>Damage</option>
                            <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Return</option>
                            <option value="transfer_in" {{ request('type') == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                            <option value="transfer_out" {{ request('type') == 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                        </select>
                    </div>

                    {{-- User --}}
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Performed By</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-magnify"></i> Search</button>
                        <a href="{{ route('warehouse.stocks.history', $product->id) }}" class="btn btn-light border" title="Reset"><i class="mdi mdi-refresh"></i></a>
                    </div>
                </form>
            </div>
        </div>

        {{-- HISTORY TABLE --}}
        <div class="card border-0 shadow-lg">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Date & Time</th>
                            <th class="py-3">Transaction Type</th>
                            <th class="py-3">Batch Info</th>
                            <th class="py-3 text-end">Quantity Change</th>
                            <th class="py-3 text-end">Balance</th>
                            <th class="py-3">Reference / Remarks</th>
                            <th class="px-4 py-3 text-end">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-dark">{{ $txn->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $txn->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @php
                                        $badges = [
                                            'purchase' => 'bg-success-subtle text-success border border-success',
                                            'sale' => 'bg-primary-subtle text-primary border border-primary',
                                            'return' => 'bg-info-subtle text-info border border-info',
                                            'damage' => 'bg-danger-subtle text-danger border border-danger',
                                            'adjustment' => 'bg-warning-subtle text-warning border border-warning',
                                            'transfer_in' => 'bg-info-subtle text-info',
                                            'transfer_out' => 'bg-secondary-subtle text-secondary'
                                        ];
                                        $class = $badges[$txn->type] ?? 'bg-light text-dark border';
                                    @endphp
                                    <span class="badge {{ $class }} px-2 py-1 rounded-pill text-uppercase">
                                        {{ str_replace('_', ' ', $txn->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($txn->batch)
                                        <div class="d-flex flex-column">
                                            <code class="text-dark fw-bold">{{ $txn->batch->batch_number }}</code>
                                            @if($txn->batch->expiry_date)
                                                <small class="text-danger" style="font-size: 0.75rem;">Exp: {{ $txn->batch->expiry_date->format('M d, Y') }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold fs-6 {{ $txn->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $txn->quantity_change > 0 ? '+' : '' }}{{ number_format($txn->quantity_change, 2) }}
                                    </span>
                                    <small class="text-muted d-block">{{ $product->unit }}</small>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    {{ number_format($txn->running_balance, 2) }}
                                </td>
                                <td>
                                    @if($txn->reference_id)
                                        <div class="small fw-bold text-dark mb-1">Ref: {{ $txn->reference_id }}</div>
                                    @endif
                                    <div class="small text-muted text-wrap" style="max-width: 250px;">
                                        {{ $txn->remarks ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="text-end">
                                            <div class="small fw-bold text-dark">{{ $txn->user->name ?? 'System' }}</div>
                                            <div class="small text-muted" style="font-size: 0.7rem;">{{ $txn->user->role ?? 'Admin' }}</div>
                                        </div>
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px;">
                                            <i class="mdi mdi-account text-secondary"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="bg-light rounded-circle p-3 mb-3">
                                            <i class="mdi mdi-file-search-outline text-muted fs-1"></i>
                                        </div>
                                        <h6 class="text-dark fw-bold">No Records Found</h6>
                                        <p class="text-muted small mb-0">Try adjusting your filters to see more results.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>