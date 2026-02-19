@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Inventory Health</h1>
        <a href="{{ route('warehouse.reports.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <!-- Low Stock -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">⚠️ Low Stock Alerts (Warehouse)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems as $product)
                                <tr>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->warehouse_qty }}</td>
                                    <td><span class="badge badge-danger">Low</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No low stock items.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Batches -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">⏳ Expiring Soon (Next 30 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Expiry Date</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringBatches as $batch)
                                <tr>
                                    <td>{{ $batch->product->product_name ?? 'N/A' }}</td>
                                    <td>{{ $batch->batch_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($batch->expiry_date)->format('Y-m-d') }}</td>
                                    <td>{{ $batch->quantity }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No batches expiring soon.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

         <!-- Expired Batches -->
         <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4 border-left-dark">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">🛑 Expired Batches</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th>Expiry Date</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiredBatches as $batch)
                                <tr>
                                    <td>{{ $batch->product->product_name ?? 'N/A' }}</td>
                                    <td>{{ $batch->batch_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($batch->expiry_date)->format('Y-m-d') }}</td>
                                    <td>{{ $batch->quantity }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No expired batches.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
