<x-app-layout title="Recall Stock Requests">

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Recall Stock Requests</h4>
            <small class="text-muted">Manage stock recall from stores</small>
        </div>
        <a href="{{ route('warehouse.stock-control.recall.create') }}" class="btn btn-primary">
            <i class="mdi mdi-plus"></i> Initiate Recall
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
                            <td>#{{ $request->id }}</td>
                            <td>{{ $request->store->store_name }}</td>
                            <td>{{ $request->product->product_name }}</td>
                            <td>{{ $request->requested_quantity }}</td>
                            <td>
                                <span class="badge bg-{{ $request->status == 'pending_store_approval' ? 'warning' : ($request->status == 'completed' ? 'success' : 'primary') }}">
                                    {{ ucwords(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td>{{ $request->initiator->name ?? 'System' }}</td>
                            <td>{{ $request->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('warehouse.stock-control.recall.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                    View & Process
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No recall requests found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </div>
    </div>

</div>

</x-app-layout>