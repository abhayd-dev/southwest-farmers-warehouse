@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Purchase Price Variance (PPV)</h1>
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
                            href="{{ route('warehouse.reports.export', ['report' => 'purchase-price-variance', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'purchase-price-variance', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'purchase-price-variance', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.reports.purchase-price-variance') }}" class="form-inline">
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
                                <th>Item Name</th>
                                <th>Standard Cost</th>
                                <th>Actual Unit Price</th>
                                <th>Price Diff</th>
                                <th>Diff %</th>
                                <th>Total Margin Impact</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($variances as $transaction)
                                @php
                                    $standardCost = $transaction->product->cost_price ?? 0;
                                    $actualPrice = $standardCost; // Placeholder as actual transaction cost might be in separate field
                                    $diff = $actualPrice - $standardCost;
                                @endphp
                                <tr>
                                    <td>{{ $transaction->product->product_name }}</td>
                                    <td>${{ number_format($standardCost, 2) }}</td>
                                    <td>${{ number_format($actualPrice, 2) }}</td>
                                    <td class="{{ $diff > 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                                        ${{ number_format($diff, 2) }}
                                    </td>
                                    <td>0%</td>
                                    <td>$0.00</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $variances->links() }}
            </div>
        </div>
    </div>
@endsection
