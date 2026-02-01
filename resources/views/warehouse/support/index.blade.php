<x-app-layout title="Support Desk">
    <div class="container-fluid">

        {{-- Breadcrumb --}}
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb mb-0 bg-white p-3 rounded shadow-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark">
                                <i class="mdi mdi-home-outline me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('warehouse.index') }}" class="text-decoration-none text-dark">
                                Warehouse
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('warehouse.support.index') }}" class="text-decoration-none text-dark">
                                Support Desk
                            </a>
                        </li>
                        <li class="breadcrumb-item active fw-bold" aria-current="page">
                            Support Tickets
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Metrics Row --}}
        <div class="row g-3 mb-4">
            {{-- Overdue --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">Overdue Tickets</h6>
                                <h2 class="text-danger fw-bold mb-0">{{ $metrics['overdue'] }}</h2>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                                <i class="mdi mdi-clock-alert-outline fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Open --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">Open Tickets</h6>
                                <h2 class="text-primary fw-bold mb-0">{{ $metrics['open'] }}</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="mdi mdi-ticket-confirmation-outline fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Critical --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted small fw-bold mb-1">Critical Issues</h6>
                                <h2 class="text-warning fw-bold mb-0">{{ $metrics['critical'] }}</h2>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                                <i class="mdi mdi-alert-decagram-outline fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & List --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-format-list-bulleted me-2 text-primary"></i>All Tickets
                </h5>
                <form class="d-flex gap-2" method="GET">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-filter-variant"></i>
                        </span>
                        <select name="status" class="form-select border-start-0 ps-0" onchange="this.form.submit()"
                            style="min-width: 150px;">
                            <option value="">All Status</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting
                            </option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved
                            </option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Store</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>SLA Due</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('warehouse.support.show', $ticket->id) }}"
                                        class="fw-bold text-primary text-decoration-none">
                                        {{ $ticket->ticket_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2">
                                            <span class="avatar-title rounded-circle bg-light text-primary">
                                                <i class="mdi mdi-store"></i>
                                            </span>
                                        </div>
                                        <span>{{ $ticket->store->store_name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>{{ Str::limit($ticket->subject, 35) }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'open' => 'success',
                                            'in_progress' => 'info',
                                            'waiting' => 'warning',
                                            'resolved' => 'primary',
                                            'closed' => 'secondary',
                                        ];
                                    @endphp
                                    <span
                                        class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusColors[$ticket->status] ?? 'secondary' }} border border-{{ $statusColors[$ticket->status] ?? 'secondary' }} border-opacity-25">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $ticket->priority == 'critical' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : 'info') }} bg-opacity-10 text-{{ $ticket->priority == 'critical' ? 'danger' : ($ticket->priority == 'high' ? 'warning' : 'info') }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td class="{{ $ticket->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                    <i class="mdi mdi-clock-outline me-1"></i>
                                    {{ $ticket->sla_due_at->diffForHumans() }}
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('warehouse.support.show', $ticket->id) }}"
                                        class="btn btn-sm btn-light text-primary shadow-sm border">
                                        View Details <i class="mdi mdi-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-inbox-outline fs-1 d-block mb-2"></i>
                                        No tickets found matching your criteria.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
