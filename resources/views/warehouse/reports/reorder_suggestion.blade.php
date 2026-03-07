@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Reorder Suggestion Report</h1>
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
                            href="{{ route('warehouse.reports.export', ['report' => 'reorder-suggestion', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'reorder-suggestion', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'reorder-suggestion', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('warehouse.reports.reorder-suggestion') }}" class="form-inline">
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
                                <th>Item Name</th>
                                <th>Current Stock</th>
                                <th>Sales Velocity (30d)</th>
                                <th>Reorder Point</th>
                                <th>Min Level</th>
                                <th>Max Level</th>
                                <th>Suggested Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suggestions as $product)
                                @php
                                    $currentStock = $product->stock->quantity ?? 0;
                                    // Placeholder calculation
                                    $suggested = max(0, 50 - $currentStock);
                                @endphp
                                <tr>
                                    <td>{{ $product->upc ?? $product->sku }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $currentStock }}</td>
                                    <td>15 Units</td>
                                    <td>10</td>
                                    <td>5</td>
                                    <td>50</td>
                                    <td class="font-weight-bold text-primary">{{ $suggested }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $suggestions->links() }}
            </div>
        </div>
    </div>
@endsection
