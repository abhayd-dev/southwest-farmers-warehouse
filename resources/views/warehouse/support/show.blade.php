<x-app-layout title="Ticket #{{ $ticket->ticket_number }}">
    <div class="container-fluid">

        {{-- Breadcrumb --}}
        <div class="row mb-3">
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
                            Ticket #{{ $ticket->ticket_number }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="mb-3">
            <a href="{{ route('warehouse.support.index') }}" class="btn btn-secondary shadow-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Tickets
            </a>
        </div>

        <div class="row">
            {{-- Left Column: Chat & Info --}}
            <div class="col-lg-8">

                {{-- Ticket Header Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fw-bold text-dark mb-1">{{ $ticket->subject }}</h4>
                                <div class="d-flex align-items-center gap-3 text-muted small">
                                    <span><i class="mdi mdi-calendar me-1"></i>
                                        {{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                                    <span><i class="mdi mdi-account me-1"></i>
                                        {{ $ticket->createdBy->name ?? 'User' }}</span>
                                    <span><i class="mdi mdi-tag me-1"></i> {{ $ticket->category }}</span>
                                </div>
                            </div>
                            @php
                                $statusColors = [
                                    'open' => 'success',
                                    'in_progress' => 'info',
                                    'waiting' => 'warning',
                                    'resolved' => 'primary',
                                    'closed' => 'secondary',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$ticket->status] ?? 'secondary' }} fs-6 px-3 py-2">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <div class="bg-light p-3 rounded-3 border">
                            <h6 class="fw-bold text-muted small text-uppercase mb-2">Description</h6>
                            <p class="mb-0 text-dark">{{ $ticket->description }}</p>
                        </div>
                    </div>
                </div>

                {{-- Chat Timeline --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold"><i
                                class="mdi mdi-message-text-outline me-2"></i>Conversation</h5>
                    </div>

                    <div class="card-body bg-light" style="max-height: 500px; overflow-y: auto;">
                        @foreach ($ticket->messages as $msg)
                            @php
                                $isInternal = $msg->is_internal;
                                $isMe = $msg->sender_type == 'App\Models\WareUser'; // Warehouse Staff
                            @endphp

                            <div class="d-flex mb-4 {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}"
                                    style="max-width: 85%;">

                                    <div class="d-flex align-items-center mb-1">
                                        @if (!$isMe)
                                            <div class="avatar-xs me-2">
                                                <span
                                                    class="avatar-title rounded-circle bg-white text-primary border shadow-sm">
                                                    {{ substr($msg->sender->name ?? 'U', 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                        <small class="fw-bold {{ $isMe ? 'text-dark' : 'text-primary' }}">
                                            {{ $msg->sender->name ?? 'Unknown' }}
                                        </small>
                                        <small class="text-muted ms-2" style="font-size: 0.75rem;">
                                            {{ $msg->created_at->diffForHumans() }}
                                        </small>
                                        @if ($isInternal)
                                            <span class="badge bg-warning text-dark ms-2"
                                                style="font-size: 0.65rem;">INTERNAL NOTE</span>
                                        @endif
                                    </div>

                                    <div class="card border-0 shadow-sm p-3 {{ $isInternal ? 'bg-warning bg-opacity-10 border border-warning' : ($isMe ? 'bg-white' : 'bg-primary bg-opacity-10 text-dark') }}"
                                        style="border-radius: {{ $isMe ? '15px 0 15px 15px' : '0 15px 15px 15px' }};">

                                        <p class="mb-0">{{ $msg->message }}</p>

                                        @if ($msg->attachments->count() > 0)
                                            <div class="mt-2 pt-2 border-top border-secondary border-opacity-25">
                                                @foreach ($msg->attachments as $att)
                                                    <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                                                        class="d-inline-flex align-items-center badge bg-white text-dark border p-2 me-1 text-decoration-none">
                                                        <i class="mdi mdi-paperclip me-1"></i> {{ $att->file_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Reply Form --}}
                    @if ($ticket->status !== 'closed')
                        <div class="card-footer bg-white p-3">
                            <form action="{{ route('warehouse.support.reply', $ticket->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <label class="form-label fw-bold text-muted small">Write a Reply</label>
                                <textarea name="message" class="form-control mb-3" rows="3" placeholder="Type your response here..." required></textarea>

                                <div
                                    class="d-flex justify-content-between align-items-center bg-light p-2 rounded border">
                                    <div class="d-flex align-items-center gap-3">
                                        {{-- File Upload --}}
                                        <div class="position-relative">
                                            <button type="button" class="btn btn-sm btn-light border"
                                                onclick="document.getElementById('attInput').click()">
                                                <i class="mdi mdi-paperclip me-1"></i> Attach Files
                                            </button>
                                            <input type="file" id="attInput" name="attachments[]" multiple
                                                class="d-none"
                                                onchange="document.getElementById('fileCount').innerText = this.files.length + ' files selected'">
                                            <span id="fileCount" class="small text-muted ms-1"></span>
                                        </div>

                                        {{-- Internal Checkbox --}}
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_internal"
                                                id="internalCheck">
                                            <label class="form-check-label small fw-bold text-warning"
                                                for="internalCheck">Internal Note</label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="mdi mdi-send me-1"></i> Send Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card-footer bg-light text-center py-3">
                            <span class="text-muted"><i class="mdi mdi-lock me-1"></i> This ticket is closed. No further
                                replies allowed.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="col-lg-4">

                {{-- Action Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0">Ticket Actions</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('warehouse.support.update', $ticket->id) }}" method="POST"
                            id="ticketUpdateForm">
                            @csrf @method('PUT')

                            {{-- Status Change --}}
                            <div class="mb-4">
                                <label class="form-label small text-muted fw-bold text-uppercase">Current
                                    Status</label>
                                <select name="status" class="form-select action-select" data-type="status">
                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="waiting" {{ $ticket->status == 'waiting' ? 'selected' : '' }}>Waiting on
                                        Store</option>
                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved
                                    </option>
                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed
                                    </option>
                                </select>
                            </div>

                            {{-- Assign Staff --}}
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-bold text-uppercase">Assigned
                                    Staff</label>
                                <select name="assigned_to_id" class="form-select action-select"
                                    data-type="assignment">
                                    <option value="">-- Unassigned --</option>
                                    @foreach ($staff as $u)
                                        <option value="{{ $u->id }}"
                                            {{ $ticket->assigned_to_id == $u->id ? 'selected' : '' }}>{{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- SLA Info Card --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold mb-0">SLA & Store Info</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted small">Store Name</span>
                                <span class="fw-bold text-dark">{{ $ticket->store->store_name ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted small">Priority</span>
                                <span class="badge bg-{{ $ticket->priority == 'critical' ? 'danger' : 'info' }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <span class="text-muted small">SLA Due Date</span>
                                <div class="text-end">
                                    <div class="fw-bold {{ $ticket->isOverdue() ? 'text-danger' : 'text-dark' }}">
                                        {{ $ticket->sla_due_at->format('d M, h:i A') }}
                                    </div>
                                    @if ($ticket->isOverdue())
                                        <small class="text-danger fw-bold">OVERDUE</small>
                                    @endif
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const selects = document.querySelectorAll('.action-select');
                const form = document.getElementById('ticketUpdateForm');

                selects.forEach(select => {
                    // Store initial value
                    select.dataset.original = select.value;

                    select.addEventListener('change', function() {
                        const type = this.dataset.type;
                        const newValueText = this.options[this.selectedIndex].text;
                        const originalValue = this.dataset.original;
                        const element = this;

                        let title = type === 'status' ? 'Update Status?' : 'Assign Staff?';
                        let text = type === 'status' ?
                            `Are you sure you want to change the status to "${newValueText}"?` :
                            `Are you sure you want to assign this ticket to "${newValueText}"?`;

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, update it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // If user confirmed, submit the form
                                form.submit();
                            } else {
                                // If cancelled, revert the select to original value
                                element.value = originalValue;
                            }
                        });
                    });
                });

            });
        </script>
    @endpush
</x-app-layout>
