<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="mdi mdi-truck-delivery me-2"></i> Stock Requests
                </h4>
                <p class="text-muted mb-0 small mt-1">Manage incoming stock requests from stores</p>
            </div>
            <div>
                <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-light' }}">Pending</a>
                <a href="{{ route('warehouse.stock-requests.index', ['status' => 'dispatched']) }}" class="btn btn-sm {{ request('status') == 'dispatched' ? 'btn-primary' : 'btn-light' }}">In Transit</a>
                <a href="{{ route('warehouse.stock-requests.index', ['status' => 'history']) }}" class="btn btn-sm {{ request('status') == 'history' ? 'btn-primary' : 'btn-light' }}">History</a>
            </div>
        </div>

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4">Store</th>
                                <th>Product</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th>Store Stock</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr>
                                <td class="ps-4">
                                    <h6 class="mb-0 fw-bold">{{ $req->store->store_name }}</h6>
                                    <small class="text-muted">{{ $req->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($req->product->image)
                                            <img src="{{ asset('storage/'.$req->product->image) }}" class="rounded me-2" width="30">
                                        @endif
                                        <div>
                                            <span class="d-block text-dark fw-medium">{{ $req->product->name }}</span>
                                            <small class="text-muted">{{ $req->product->sku }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $req->requested_quantity }}</span>
                                    @if($req->fulfilled_quantity > 0)
                                        <div class="mt-1 small text-success">
                                            <i class="mdi mdi-check"></i> Sent: {{ $req->fulfilled_quantity }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($req->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($req->status == 'partial')
                                        <span class="badge bg-info">Partial</span>
                                    @elseif($req->status == 'dispatched')
                                        <span class="badge bg-primary">In Transit</span>
                                    @elseif($req->status == 'completed')
                                        <span class="badge bg-success">Received</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->isLowStock())
                                        <span class="text-danger fw-bold"><i class="mdi mdi-alert"></i> Low ({{ $req->storeStock->quantity ?? 0 }})</span>
                                    @else
                                        <span class="text-muted">OK ({{ $req->storeStock->quantity ?? 0 }})</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('warehouse.stock-requests.show', $req->id) }}" class="btn btn-sm btn-outline-primary">
                                        View & Dispatch
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <img src="{{ asset('assets/images/no-data.svg') }}" height="80" class="opacity-50">
                                    <p class="text-muted mt-2">No requests found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</x-app-layout>