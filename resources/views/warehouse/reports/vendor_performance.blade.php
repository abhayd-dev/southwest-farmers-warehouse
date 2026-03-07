@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Vendor Performance Scorecard</h1>
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
                            href="{{ route('warehouse.reports.export', ['report' => 'vendor-performance', 'format' => 'pdf']) }}">PDF</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'vendor-performance', 'format' => 'excel']) }}">Excel</a>
                        <a class="dropdown-item"
                            href="{{ route('warehouse.reports.export', ['report' => 'vendor-performance', 'format' => 'csv']) }}">CSV</a>
                    </div>
                </div>
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
                                <th>POs Sent</th>
                                <th>Lead Time (Avg Days)</th>
                                <th>Fill Rate %</th>
                                <th>Damaged Items</th>
                                <th>Total Spend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendors as $vendor)
                                <tr>
                                    <td>{{ $vendor->name }}</td>
                                    <td>{{ $vendor->po_count }}</td>
                                    <td>5.2 Days</td> {{-- Placeholder for calculated logic --}}
                                    <td>92%</td> {{-- Placeholder for calculated logic --}}
                                    <td>12</td> {{-- Placeholder for calculated logic --}}
                                    <td>${{ number_format($vendor->purchaseOrders->sum('total_amount'), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
