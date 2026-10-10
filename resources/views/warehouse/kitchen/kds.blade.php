<x-app-layout title="Kitchen Display System (KDS) | Warehouse POS">

    <div class="container-fluid px-3 px-lg-4 py-3 h-100 d-flex flex-column" style="min-height: calc(100vh - 70px);">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">Kitchen Display System (KDS)</h4>
                <p class="text-muted mb-0 small">Live view of active incoming kitchen orders.</p>
            </div>
            <div>
                <button class="btn btn-primary btn-sm shadow-sm" onclick="location.reload()">
                    <i class="mdi mdi-refresh me-1"></i>Refresh Board
                </button>
            </div>
        </div>

        <div class="row flex-grow-1 flex-nowrap overflow-auto pb-3 gx-3 kds-board" style="min-height: 500px;">

            @php
                $tz = config('app.display_timezone', 'America/Chicago');
                // Spec 5.2: Incoming -> Accepted -> Preparing -> Ready -> Pickup/Delivery Handoff -> Completed
                $columns = [
                    'New' => ['bg' => 'primary', 'icon' => 'mdi-alert-decagram-outline', 'title' => 'Incoming'],
                    'Accepted' => ['bg' => 'info', 'icon' => 'mdi-thumb-up-outline', 'title' => 'Accepted'],
                    'Preparing' => ['bg' => 'warning', 'icon' => 'mdi-chef-hat', 'title' => 'Preparing'],
                    'Ready' => ['bg' => 'success', 'icon' => 'mdi-check-decagram-outline', 'title' => 'Ready'],
                    'Handoff' => ['bg' => 'dark', 'icon' => 'mdi-hand-extended-outline', 'title' => 'Handoff'],
                ];
                $next = [
                    'New' => ['Accepted', 'Accept', 'btn-info'],
                    'Accepted' => ['Preparing', 'Start Prep', 'btn-warning text-dark fw-bold'],
                    'Preparing' => ['Ready', 'Mark Ready', 'btn-success'],
                    'Ready' => ['Handoff', 'Hand Off (pickup / delivery)', 'btn-dark'],
                    'Handoff' => ['Completed', 'Complete', 'btn-outline-dark'],
                ];
            @endphp

            @foreach($columns as $status => $details)
            <div class="col-12 col-md-4 col-lg d-flex flex-column" style="min-width: 280px;">
                <div class="card bg-light border-0 shadow-sm rounded-3 flex-grow-1 d-flex flex-column">
                    <div class="card-header border-bottom border-{{ $details['bg'] }} border-3 bg-white py-3 rounded-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark text-uppercase">
                                <i class="mdi {{ $details['icon'] }} text-{{ $details['bg'] }} fs-5 align-middle me-1"></i> {{ $details['title'] }}
                            </h6>
                            <span class="badge bg-{{ $details['bg'] }} rounded-pill">{{ count($kanbanData[$status]) }}</span>
                        </div>
                    </div>

                    <div class="card-body p-2 flex-grow-1 overflow-auto kds-column" data-status="{{ $status }}" style="max-height: calc(100vh - 180px);">
                        @forelse($kanbanData[$status] as $order)
                        @php $overdue = $order->due_at && $order->due_at->isPast(); @endphp
                        <div class="card border-0 shadow-sm mb-2 rounded-3 ticket-card {{ $overdue ? 'border border-danger' : '' }}" data-id="{{ $order->id }}">
                            <div class="card-header bg-white border-0 pb-1 pt-2 px-3 d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">#{{ $order->invoice_number }}</h6>
                                    <small class="text-muted">Placed {{ $order->created_at?->displayTime()->format('h:i A') ?? '—' }}</small>
                                    <small class="d-block fw-semibold text-primary"><i class="mdi mdi-store-outline"></i> {{ $order->store->store_name ?? 'Store #' . $order->store_id }}</small>
                                    @if ($order->customer?->name)
                                        <small class="d-block text-muted"><i class="mdi mdi-account-outline"></i> {{ $order->customer->name }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary-subtle text-primary border d-block mb-1">{{ ucfirst($order->source ?? 'pos') === 'Pos' ? 'POS' : ucfirst($order->source ?? 'POS') }}</span>
                                    @if ($order->order_type)
                                        <span class="badge {{ $order->order_type === 'Catering' ? 'bg-warning text-dark' : 'bg-dark bg-opacity-10 text-dark border' }}">{{ $order->order_type }}</span>
                                    @endif
                                    @if ($order->guest_count)
                                        <small class="d-block text-muted mt-1">{{ $order->guest_count }} guests</small>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body px-3 py-2">
                                @if ($order->due_at)
                                    <div class="small fw-bold mb-2 {{ $overdue ? 'text-danger' : 'text-primary' }}">
                                        <i class="mdi mdi-clock-outline"></i> Due {{ $order->due_at->copy()->timezone($tz)->format('m/d h:i A') }}{{ $overdue ? ' (overdue)' : '' }}
                                    </div>
                                @endif
                                <ul class="list-unstyled mb-2 small">
                                    @foreach($order->items as $item)
                                    <li class="mb-1 fw-semibold text-secondary">
                                        <span class="text-dark">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}x</span> {{ $item->menuItem->name ?? ($item->product->product_name ?? 'Unknown Item') }}
                                    </li>
                                    @endforeach
                                </ul>
                                @if($order->special_instructions)
                                    <div class="alert alert-warning py-1 px-2 mb-2 small border-0 text-dark fw-bold">
                                        <i class="mdi mdi-alert me-1"></i> {{ $order->special_instructions }}
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-2 px-3 d-flex gap-2">
                                @php [$to, $label, $cls] = $next[$status]; @endphp
                                <button class="btn {{ $cls }} btn-sm flex-grow-1 update-status" data-id="{{ $order->id }}" data-status="{{ $to }}">{{ $label }}</button>
                                <button class="btn btn-outline-danger btn-sm cancel-order" data-id="{{ $order->id }}" data-invoice="{{ $order->invoice_number }}" title="Cancel order"><i class="mdi mdi-close"></i></button>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="mdi mdi-inbox-outline fs-1 mb-2 d-block opacity-50"></i>
                            No orders in this queue.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cancelReasons = @json($cancelReasons);

            function send(orderId, body, btn) {
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                }
                return fetch(`/kitchen/kds/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        location.reload();
                    } else {
                        Swal.fire('Not updated', (data && (data.message || (data.errors && Object.values(data.errors)[0][0]))) || 'Could not update the order.', 'error')
                            .then(() => location.reload());
                    }
                })
                .catch(() => Swal.fire('Error', 'Could not reach the server. Check the connection.', 'error').then(() => location.reload()));
            }

            document.querySelectorAll('.update-status').forEach(btn => btn.addEventListener('click', function() {
                send(this.dataset.id, { status: this.dataset.status }, this);
            }));

            // Cancel needs a reason (kitchen spec 5.2).
            document.querySelectorAll('.cancel-order').forEach(btn => btn.addEventListener('click', function() {
                const id = this.dataset.id;
                Swal.fire({
                    title: `Cancel order #${this.dataset.invoice}?`,
                    input: 'select',
                    inputOptions: Object.fromEntries(cancelReasons.map(r => [r, r])),
                    inputPlaceholder: 'Choose a reason',
                    showCancelButton: true,
                    confirmButtonText: 'Cancel order',
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Keep order',
                    inputValidator: v => !v && 'Choose a reason for cancelling.'
                }).then(r => { if (r.isConfirmed) send(id, { status: 'Cancelled', reason: r.value }); });
            }));

            // Auto-refresh, but not while a cancel dialog is open.
            setInterval(function() {
                if (!Swal.isVisible()) location.reload();
            }, 30000);
        });
    </script>
    @endpush
    <style>
        .kds-board::-webkit-scrollbar { height: 8px; }
        .kds-board::-webkit-scrollbar-thumb { background-color: #dee2e6; border-radius: 10px; }
        .kds-column::-webkit-scrollbar { width: 4px; }
        .kds-column::-webkit-scrollbar-thumb { background-color: #dee2e6; border-radius: 10px; }
        .ticket-card { border-left: 4px solid transparent !important; transition: all 0.2s ease; }
        .ticket-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        [data-status="New"] .ticket-card { border-left-color: #556ee6 !important; }
        [data-status="Accepted"] .ticket-card { border-left-color: #50a5f1 !important; }
        [data-status="Preparing"] .ticket-card { border-left-color: #f1b44c !important; }
        [data-status="Ready"] .ticket-card { border-left-color: #34c38f !important; }
        [data-status="Handoff"] .ticket-card { border-left-color: #343a40 !important; }
    </style>
</x-app-layout>
