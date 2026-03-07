@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Open Purchase Orders Report</h1>
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
                            href="{{ route('warehouse.reports.export', ['report' => 'open-purchase-orders', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'open-purchase-orders', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'open-purchase-orders', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.reports.open-purchase-orders') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">Vendor:</label>
                        <select name="vendor_id" class="form-control form-control-sm mr-2">
                            <option value="">All Vendors</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ $vendorId == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
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
                                <th>PO Number</th>
                                <th>Vendor Name</th>
                                <th>Date Created</th>
                                <th>Expected Delivery</th>
                                <th>Total PO Value</th>
                                <th>Total Units Ordered</th>
                                <th>Remaining Units</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $totalUnits = $order->items->sum('requested_quantity');
                                    $remainingUnits = $order->items->sum(function ($item) {
                                        return max(0, $item->requested_quantity - $item->received_quantity);
                                    });
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('warehouse.purchase-orders.show', $order->id) }}"
                                            class="font-weight-bold">
                                            {{ $order->po_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->vendor->name }}</td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td>{{ $order->expected_delivery_date ? $order->expected_delivery_date->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>{{ $totalUnits }}</td>
                                    <td>{{ $remainingUnits }}</td>
                                    <td>
                                        <span
                                            class="badge badge-{{ ($order->status ?? 'pending') == 'ordered' ? 'primary' : 'warning' }}">
                                            {{ strtoupper($order->status ?? 'pending') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
