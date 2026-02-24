<x-app-layout title="Store Orders">

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-store text-primary"></i>
                            Store Purchase Orders
                        </h4>
                        <p class="text-muted mb-0 small">Manage store orders (PO-based)</p>
                    </div>
                    <div class="flex-shrink-0">
                        <form method="GET" action="{{ route('warehouse.store-orders.index') }}" class="d-flex gap-2 align-items-center">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search PO#, Store..."
                                       value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                            </div>
                            @if(request('search'))
                                <a href="{{ route('warehouse.store-orders.index', ['status' => request('status', 'pending')]) }}"
                                   class="btn btn-outline-secondary">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABS --}}
        <ul class="nav nav-tabs mb-4">
            @foreach([
                'pending'    => ['label' => 'Pending',    'count' => $pendingCount,    'color' => 'warning'],
                'approved'   => ['label' => 'Approved',   'count' => $approvedCount,   'color' => 'primary'],
                'dispatched' => ['label' => 'Dispatched', 'count' => $dispatchedCount, 'color' => 'info'],
                'completed'  => ['label' => 'Completed',  'count' => $completedCount,  'color' => 'success'],
                'rejected'   => ['label' => 'Rejected',   'count' => $rejectedCount,   'color' => 'danger'],
                'all'        => ['label' => 'All',        'count' => null,             'color' => 'secondary'],
            ] as $key => $tab)
                <li class="nav-item">
                    <a class="nav-link {{ $status === $key ? 'active fw-bold' : '' }}"
                       href="{{ route('warehouse.store-orders.index', ['status' => $key]) }}">
                        {{ $tab['label'] }}
                        @if($tab['count'] !== null && $tab['count'] > 0)
                            <span class="badge bg-{{ $tab['color'] }} ms-1">{{ $tab['count'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- STATS CARDS --}}
        <div class="row g-3 mb-4">
            @foreach([
                ['label' => 'Pending',    'count' => $pendingCount,    'color' => 'warning', 'icon' => 'clock-outline'],
                ['label' => 'Approved',   'count' => $approvedCount,   'color' => 'primary', 'icon' => 'check-circle-outline'],
                ['label' => 'Dispatched', 'count' => $dispatchedCount, 'color' => 'info',    'icon' => 'truck'],
                ['label' => 'Completed',  'count' => $completedCount,  'color' => 'success', 'icon' => 'check-all'],
            ] as $stat)
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm rounded-circle bg-{{ $stat['color'] }} bg-opacity-10 d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-{{ $stat['icon'] }} text-{{ $stat['color'] }} fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1 small">{{ $stat['label'] }}</p>
                                    <h4 class="mb-0 fw-bold text-{{ $stat['color'] }}">{{ $stat['count'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="mdi mdi-format-list-bulleted text-primary"></i>
                    Store Orders List
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-semibold small">#</th>
                                <th class="px-4 py-3 text-muted fw-semibold small">PO Number</th>
                                <th class="py-3 text-muted fw-semibold small">Date</th>
                                <th class="py-3 text-muted fw-semibold small">Store</th>
                                <th class="py-3 text-muted fw-semibold small text-center">Items</th>
                                <th class="py-3 text-muted fw-semibold small text-center">Status</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-muted">
                                        {{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}
                                    </td>
                                    <td class="px-4 py-3 fw-bold text-primary">{{ $order->po_number }}</td>
                                    <td class="py-3">
                                        <div class="fw-semibold">{{ $order->request_date->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-semibold text-dark">{{ $order->store->store_name }}</div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-secondary">{{ $order->items->count() }} items</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        @php
                                            $badges = [
                                                'pending'    => 'bg-warning text-dark',
                                                'approved'   => 'bg-primary',
                                                'dispatched' => 'bg-info',
                                                'completed'  => 'bg-success',
                                                'rejected'   => 'bg-danger',
                                            ];
                                            $badgeClass = $badges[$order->status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ strtoupper($order->status) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <x-action-buttons :viewUrl="route('warehouse.store-orders.show', $order->id)" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="mdi mdi-store text-muted" style="font-size: 4rem;"></i>
                                            <p class="text-muted mt-3 mb-0">No store orders found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($orders->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} entries
                        </div>
                        <div>{{ $orders->appends(request()->query())->links() }}</div>
                    </div>
                </div>
            @endif
        </div>

    </div>

</x-app-layout>
