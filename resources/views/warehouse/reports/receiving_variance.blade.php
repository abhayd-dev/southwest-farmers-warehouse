@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Receiving Variance Report</h1>
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
                            href="{{ route('warehouse.reports.export', ['report' => 'receiving-variance', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'receiving-variance', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'receiving-variance', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.reports.receiving-variance') }}" class="form-inline">
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
                        <label class="mr-2">Category:</label>
                        <select name="category_id" class="form-control form-control-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
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
                                <th>PO Number</th>
                                <th>SKU/Item Name</th>
                                <th>Qty Ordered</th>
                                <th>Qty Received</th>
                                <th>Variance</th>
                                <th>Unit Cost</th>
                                <th>Total Cost Diff</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($variances as $item)
                                @php
                                    $variance = $item->requested_quantity - $item->received_quantity;
                                    $costDiff = $variance * $item->unit_cost;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('warehouse.purchase-orders.show', $item->purchaseOrder->id) }}">
                                            {{ $item->purchaseOrder->po_number }}
                                        </a>
                                    </td>
                                    <td>{{ $item->product->product_name }}</td>
                                    <td>{{ $item->requested_quantity }}</td>
                                    <td>{{ $item->received_quantity }}</td>
                                    <td class="text-danger font-weight-bold">{{ $variance }}</td>
                                    <td>${{ number_format($item->unit_cost, 2) }}</td>
                                    <td>${{ number_format($costDiff, 2) }}</td>
                                    <td>{{ $variance > 0 ? 'Shortage' : 'Overage' }}</td>
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
