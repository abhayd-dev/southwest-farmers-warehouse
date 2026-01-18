<x-app-layout title="Stock Requests">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="mdi mdi-truck-delivery me-2"></i> Stock Requests
                </h4>
                <p class="text-muted mb-0 small mt-1">Manage incoming stock requests from stores</p>
            </div>
            <div class="btn-group shadow-sm">
                <a href="{{ route('warehouse.stock-requests.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">Pending</a>
                <a href="{{ route('warehouse.stock-requests.index', ['status' => 'dispatched']) }}" class="btn btn-sm {{ request('status') == 'dispatched' ? 'btn-primary' : 'btn-outline-primary' }}">In Transit</a>
                <a href="{{ route('warehouse.stock-requests.index', ['status' => 'history']) }}" class="btn btn-sm {{ request('status') == 'history' ? 'btn-primary' : 'btn-outline-primary' }}">History</a>
            </div>
        </div>

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4">Request ID</th>
                                <th>Store</th>
                                <th>Product</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr>
                                <td class="ps-4 fw-bold">#REQ-{{ $req->id }}</td>
                                <td>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $req->store->store_name }}</h6>
                                    <small class="text-muted">{{ $req->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-light rounded me-2 d-flex align-items-center justify-content-center border">
                                            <i class="mdi mdi-box text-secondary"></i>
                                        </div>
                                        <div>
                                            <span class="d-block text-dark fw-bold small">{{ $req->product->product_name }}</span>
                                            <small class="text-muted">{{ $req->product->sku }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-primary fs-6 mb-1" style="width: fit-content;">{{ $req->requested_quantity }}</span>
                                        @if($req->fulfilled_quantity > 0)
                                            <small class="text-success fw-bold"><i class="mdi mdi-check"></i> Sent: {{ $req->fulfilled_quantity }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'pending' => ['bg-warning text-dark', 'Pending'],
                                            'partial' => ['bg-info text-white', 'Partial'],
                                            'dispatched' => ['bg-primary text-white', 'In Transit'],
                                            'completed' => ['bg-success text-white', 'Completed'],
                                            'rejected' => ['bg-danger text-white', 'Rejected'],
                                        ];
                                        $statusData = $statusMap[$req->status] ?? ['bg-secondary', $req->status];
                                    @endphp
                                    <span class="badge {{ $statusData[0] }} px-2 py-1">{{ $statusData[1] }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('warehouse.stock-requests.show', $req->id) }}" class="btn btn-sm btn-outline-dark shadow-sm">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center opacity-50">
                                        <i class="mdi mdi-clipboard-text-off fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No requests found in this category.</p>
                                    </div>
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