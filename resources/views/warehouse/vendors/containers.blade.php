<x-app-layout title="Pallets &amp; Dividers">
    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                        class="text-decoration-none"><i class="mdi mdi-home-outline"></i> Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('warehouse.vendors.index') }}"
                                        class="text-decoration-none">Vendors</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Pallets &amp; Dividers</li>
                            </ol>
                        </nav>
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-pallet text-primary"></i> Pallet &amp; Divider Inventory
                        </h4>
                        <p class="text-muted mb-0 small">
                            Running balance of returnable pallets and dividers on hand per vendor. Received and sent
                            back from each order's Receive Order screen; reconcile here if a physical count disagrees.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase fw-bold">Vendor</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold text-center">Pallets</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold text-center">Dividers</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold text-center">Last Updated</th>
                                @if (auth()->user()->can('manage_vendors'))
                                    <th class="py-3 small text-muted text-uppercase fw-bold text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vendors as $vendor)
                                @php $balance = $balances->get($vendor->id); @endphp
                                <tr>
                                    <td class="px-4 fw-semibold text-dark">{{ $vendor->name }}</td>
                                    <td class="text-center">{{ number_format($balance->pallet_count ?? 0, 2) }}</td>
                                    <td class="text-center">{{ number_format($balance->divider_count ?? 0, 2) }}</td>
                                    <td class="text-center text-muted small">
                                        {{ $balance && $balance->updated_at ? $balance->updated_at->format('d M Y, g:i A') : '-' }}
                                    </td>
                                    @if (auth()->user()->can('manage_vendors'))
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#reconcile-{{ $vendor->id }}">
                                                <i class="mdi mdi-tune-variant me-1"></i> Reconcile
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No active vendors.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if (auth()->user()->can('manage_vendors'))
            @foreach ($vendors as $vendor)
                @php $balance = $balances->get($vendor->id); @endphp
                <div class="modal fade" id="reconcile-{{ $vendor->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form method="POST"
                                action="{{ route('warehouse.vendor-containers.reconcile', $vendor->id) }}">
                                @csrf
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Reconcile: {{ $vendor->name }}</h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Pallet Count</label>
                                        <input type="number" name="pallet_count" class="form-control"
                                            min="0" step="0.01" value="{{ $balance->pallet_count ?? 0 }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Divider Count</label>
                                        <input type="number" name="divider_count" class="form-control"
                                            min="0" step="0.01" value="{{ $balance->divider_count ?? 0 }}" required>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Reason</label>
                                        <textarea name="reason" class="form-control" rows="3"
                                            placeholder="Why does the system count differ from the physical count?" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save Reconciliation</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-app-layout>
