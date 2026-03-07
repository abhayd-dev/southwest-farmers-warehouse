@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Goods Received Not Invoiced (GRNI)</h1>
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
                            href="{{ route('warehouse.reports.export', ['report' => 'grni', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'grni', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'grni', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.reports.grni') }}" class="form-inline">
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
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <!-- Summary -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Vendor Name</th>
                                <th>PO Number</th>
                                <th>Date Received</th>
                                <th>Total Units Received</th>
                                <th>Value of Received Goods</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $receivedUnits = $order->items->sum('received_quantity');
                                    $receivedValue = $order->items->sum(function ($item) {
                                        return $item->received_quantity * $item->unit_cost;
                                    });
                                @endphp
                                <tr>
                                    <td>{{ $order->vendor->name }}</td>
                                    <td>
                                        <a href="{{ route('warehouse.purchase-orders.show', $order->id) }}">
                                            {{ $order->po_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->updated_at->format('d M Y') }}</td>
                                    <td>{{ $receivedUnits }}</td>
                                    <td>${{ number_format($receivedValue, 2) }}</td>
                                    <td>
                                        <span class="badge badge-primary bg-danger">UNINVOICED</span>
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
