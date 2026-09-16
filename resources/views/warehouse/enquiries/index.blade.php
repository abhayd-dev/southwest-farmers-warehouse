<x-app-layout title="Enquiries">
    <div class="container-fluid">

        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    <nav class="mb-1">
                        <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="page-breadcrumb-link">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted">Enquiries</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-help-circle-outline text-primary"></i> Enquiries
                    </h4>
                    <p class="text-muted small mb-0">Customer enquiries escalated by a store. Brand-new enquiries stay with the store until escalated.</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" class="row g-2">
                    <div class="col-12 col-sm-6 col-md-4">
                        <select name="status" class="form-select border-light shadow-sm">
                            <option value="">All Escalated</option>
                            <option value="escalated_warehouse" {{ request('status') == 'escalated_warehouse' ? 'selected' : '' }}>Escalated to Warehouse</option>
                            <option value="escalated_admin" {{ request('status') == 'escalated_admin' ? 'selected' : '' }}>Escalated to Super Admin</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">
                            <i class="mdi mdi-filter me-1"></i> Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap w-100">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small text-muted text-uppercase fw-bold">From</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Subject</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold">Escalated</th>
                                <th class="py-3 small text-muted text-uppercase fw-bold text-center">Stage</th>
                                <th class="px-4 py-3 small text-muted text-uppercase fw-bold text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enquiries as $enquiry)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-semibold">{{ $enquiry->name }}</div>
                                        <small class="text-muted">{{ $enquiry->email }}</small>
                                    </td>
                                    <td>{{ $enquiry->subject }}</td>
                                    <td>{{ optional($enquiry->escalated_at)->format('d M Y, h:i A') }}</td>
                                    <td class="text-center">
                                        @if($enquiry->isEscalatedToAdmin())
                                            <span class="badge bg-danger">Super Admin</span>
                                        @else
                                            <span class="badge bg-warning">Warehouse</span>
                                        @endif
                                    </td>
                                    <td class="px-4 text-end">
                                        <a href="{{ route('warehouse.enquiries.show', $enquiry->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        No escalated enquiries right now.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($enquiries->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $enquiries->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
