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
                $columns = [
                    'New' => ['bg' => 'primary', 'icon' => 'mdi-alert-decagram-outline'],
                    'Accepted' => ['bg' => 'info', 'icon' => 'mdi-thumb-up-outline'],
                    'Preparing' => ['bg' => 'warning', 'icon' => 'mdi-chef-hat'],
                    'Ready' => ['bg' => 'success', 'icon' => 'mdi-check-decagram-outline']
                ];
            @endphp

            @foreach($columns as $status => $details)
            <div class="col-12 col-md-4 col-lg-3 d-flex flex-column" style="min-width: 320px;">
                <div class="card bg-light border-0 shadow-sm rounded-3 flex-grow-1 d-flex flex-column">
                    <div class="card-header border-bottom border-{{ $details['bg'] }} border-3 bg-white py-3 rounded-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark text-uppercase">
                                <i class="mdi {{ $details['icon'] }} text-{{ $details['bg'] }} fs-5 align-middle me-1"></i> {{ $status }}
                            </h6>
                            <span class="badge bg-{{ $details['bg'] }} rounded-pill">{{ count($kanbanData[$status]) }}</span>
                        </div>
                    </div>
                    
                    <div class="card-body p-2 flex-grow-1 overflow-auto kds-column" data-status="{{ $status }}" style="max-height: calc(100vh - 180px);">
                        @forelse($kanbanData[$status] as $order)
                        <div class="card border-0 shadow-sm mb-2 rounded-3 ticket-card" data-id="{{ $order->id }}">
                            <div class="card-header bg-white border-0 pb-1 pt-2 px-3 d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">#{{ $order->invoice_number }}</h6>
                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                </div>
                                <span class="badge bg-dark bg-opacity-10 text-dark border">{{ $order->order_type ?? 'Web Order' }}</span>
                            </div>
                            <div class="card-body px-3 py-2">
                                <ul class="list-unstyled mb-2 small">
                                    @foreach($order->items as $item)
                                    <li class="mb-1 fw-semibold text-secondary">
                                        <span class="text-dark">{{ $item->quantity }}x</span> {{ $item->product->product_name ?? 'Unknown Item' }}
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
                                @if($status === 'New')
                                    <button class="btn btn-info btn-sm w-100 update-status" data-id="{{ $order->id }}" data-status="Accepted">Accept</button>
                                @elseif($status === 'Accepted')
                                    <button class="btn btn-warning btn-sm w-100 update-status text-dark fw-bold" data-id="{{ $order->id }}" data-status="Preparing">Start Prep</button>
                                @elseif($status === 'Preparing')
                                    <button class="btn btn-success btn-sm w-100 update-status" data-id="{{ $order->id }}" data-status="Ready">Mark Ready</button>
                                @elseif($status === 'Ready')
                                    <button class="btn btn-dark btn-sm w-100 update-status" data-id="{{ $order->id }}" data-status="Completed">Complete (Hand-off)</button>
                                @endif
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
            const buttons = document.querySelectorAll('.update-status');
            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    const status = this.getAttribute('data-status');
                    const buttonElement = this;
                    
                    buttonElement.disabled = true;
                    buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                    fetch(`/kitchen/kds/${orderId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); 
                        } else {
                            alert('Failed to update status');
                            buttonElement.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating status');
                        buttonElement.disabled = false;
                    });
                });
            });

            // Auto-refresh KDS every 30 seconds
            setInterval(function() {
                location.reload();
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
    </style>
</x-app-layout>
