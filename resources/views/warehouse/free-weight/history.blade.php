<x-app-layout title="Packaging Event History">
    <div class="container-fluid p-3 p-md-4">

        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0"><i class="mdi mdi-history text-primary me-2"></i> Packaging Event History</h4>
                <small class="text-muted">All bulk-to-pack conversion events</small>
            </div>
            <a href="{{ route('warehouse.free-weight.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="py-3">Date</th>
                                <th class="py-3">Bulk Product</th>
                                <th class="py-3">Package</th>
                                <th class="py-3 text-danger">Bulk Used</th>
                                <th class="py-3 text-success">Packages Created</th>
                                <th class="py-3">Employee</th>
                                <th class="py-3">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                <tr>
                                    <td class="px-4 py-3 text-muted small">{{ $event->id }}</td>
                                    <td class="py-3 text-muted small">{{ $event->event_date->format('d M Y, H:i') }}</td>
                                    <td class="py-3 fw-semibold">{{ $event->freeWeightProduct->product->product_name ?? 'N/A' }}</td>
                                    <td class="py-3">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                            {{ $event->package->package_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 fw-bold text-danger">
                                        -{{ number_format($event->bulk_weight_reduced, 2) }} lbs
                                    </td>
                                    <td class="py-3 fw-bold text-success">
                                        +{{ number_format($event->packages_created) }} pcs
                                    </td>
                                    <td class="py-3">{{ $event->employee->name ?? 'System' }}</td>
                                    <td class="py-3 text-muted small">{{ $event->notes ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-history fs-1 opacity-25 d-block mb-2"></i>
                                        No packaging events recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($events->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
