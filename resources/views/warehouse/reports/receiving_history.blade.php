@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Receiving History Report</h1>
            <div>
                <a href="{{ route('warehouse.reports.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm"></i> Back
                </a>
                <div class="btn-group ml-2">
                    <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download fa-sm"></i> Export
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'receiving-history', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'receiving-history', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'receiving-history', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.reports.receiving-history') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">Vendor:</label>
                        <select name="vendor_id" class="form-control form-control-sm">
                            <option value="">All Vendors</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $vendorId == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-3">
                        <label class="mr-2">Staff:</label>
                        <select name="user_id" class="form-control form-control-sm">
                            <option value="">All Staff</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-3">
                        <label class="mr-2">From:</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="form-group mr-3">
                        <label class="mr-2">To:</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                            class="form-control form-control-sm">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Receiving Date</th>
                                <th>Staff Name</th>
                                <th>Vendor</th>
                                <th>PO Number</th>
                                <th>Item Name</th>
                                <th>Qty Received</th>
                                <th>Unit Cost</th>
                                <th>Total Value</th>
                                <th>Batch No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($history as $transaction)
                                @php
                                    $totalValue =
                                        $transaction->quantity_change * ($transaction->product->cost_price ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ $transaction->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                                    <td>{{ $transaction->vendor->name ?? 'N/A' }}</td>
                                    <td>{{ $transaction->reference_no ?? 'N/A' }}</td>
                                    <td>{{ $transaction->product->product_name }}</td>
                                    <td>{{ abs($transaction->quantity_change) }}</td>
                                    <td>${{ number_format($transaction->product->cost_price ?? 0, 2) }}</td>
                                    <td>${{ number_format($totalValue, 2) }}</td>
                                    <td>{{ $transaction->batch->batch_number ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $history->links() }}
            </div>
        </div>
    </div>
@endsection
