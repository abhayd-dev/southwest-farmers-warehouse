<x-app-layout title="Recall Stock Requests">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Recall Stock Requests'])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="mdi mdi-undo-variant text-warning me-2"></i> Recall Stock Requests
            </h4>
            <small class="text-muted">Manage reverse stock movement from stores to warehouse</small>
        </div>
        <a href="{{ route('warehouse.stock-control.recall.create') }}" class="btn btn-primary">
            <i class="mdi mdi-plus-circle me-1"></i> Initiate New Recall
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Store</th>
                            <th>Product</th>
                            <th>Requested Qty</th>
                            <th>Status</th>
                            <th>Initiated By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>#{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $request->store->store_name }}</td>
                            <td>{{ $request->product->product_name }}</td>
                            <td class="fw-bold">{{ $request->requested_quantity }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $request->status == 'pending_store_approval' ? 'warning' : 
                                    ($request->status == 'completed' ? 'success' : 
                                    ($request->status == 'rejected_by_store' ? 'danger' : 'primary'))
                                }}">
                                    {{ ucwords(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td>{{ $request->initiator->name ?? 'System' }}</td>
                            <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('warehouse.stock-control.recall.show', $request) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="mdi mdi-eye"></i> View & Process
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="mdi mdi-information-outline fs-3 d-block mb-2"></i>
                                No recall requests found at this time.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $requests->links() }}
            </div>
        </div>
    </div>

</div>

</x-app-layout>