@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Stock Movement Report</h1>
        <a href="{{ route('warehouse.reports.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Options</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('warehouse.reports.stock-movement') }}" class="form-inline">
                <div class="form-group mr-2">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="form-group mr-2">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="form-group mr-2">
                    <label for="type" class="mr-2">Type:</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transaction History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Qty Change</th>
                            <th>Balance</th>
                            <th>User</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $tx->product->product_name ?? 'Unknown' }}</td>
                            <td>
                                <span class="badge badge-{{ $tx->quantity_change > 0 ? 'success' : 'danger' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="{{ $tx->quantity_change > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $tx->quantity_change > 0 ? '+' : '' }}{{ $tx->quantity_change }}
                            </td>
                            <td>{{ $tx->running_balance }}</td>
                            <td>{{ $tx->user->name ?? 'System' }}</td>
                            <td>{{ $tx->remarks }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No transactions found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $transactions->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection
