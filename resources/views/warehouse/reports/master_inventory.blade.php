@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Master Inventory & Valuation Report</h1>
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
                            href="{{ route('warehouse.reports.export', ['report' => 'master-inventory', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'master-inventory', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'master-inventory', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.reports.master-inventory') }}" class="form-inline">
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
                                <th>SKU/UPC</th>
                                <th>Item Description</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Qty on Hand</th>
                                <th>Landed Cost</th>
                                <th>Total Valuation</th>
                                <th>Retail Price</th>
                                <th>Margin %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php
                                    $qty = $product->stock->quantity ?? 0;
                                    $valuation = $qty * $product->cost_price;
                                    $margin =
                                        $product->retail_price > 0
                                            ? (($product->retail_price - $product->cost_price) /
                                                    $product->retail_price) *
                                                100
                                            : 0;
                                @endphp
                                <tr>
                                    <td>{{ $product->upc ?? $product->sku }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                    <td>Warehouse</td>
                                    <td>{{ $qty }}</td>
                                    <td>${{ number_format($product->cost_price, 2) }}</td>
                                    <td>${{ number_format($valuation, 2) }}</td>
                                    <td>${{ number_format($product->retail_price, 2) }}</td>
                                    <td>{{ number_format($margin, 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
