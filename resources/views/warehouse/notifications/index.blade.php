<x-app-layout title="All Notifications">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="mdi mdi-bell-ring-outline text-primary"></i> Notifications
            </h4>
            
            <form action="{{ route('warehouse.notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="mdi mdi-check-all me-1"></i> Mark All as Read
                </button>
            </form>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="list-group list-group-flush">
                @forelse ($notifications as $notification)
                    <div class="list-group-item p-3 {{ $notification->read_at ? '' : 'bg-primary bg-opacity-10' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            {{-- Icon --}}
                            <div class="me-3 mt-1">
                                @php
                                    $colors = ['info' => 'primary', 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger'];
                                    $color = $colors[$notification->type] ?? 'primary';
                                @endphp
                                <div class="avatar-sm bg-{{ $color }} text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-bell fs-5"></i>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1 fw-bold text-dark">
                                        @if($notification->url)
                                            <a href="{{ $notification->url }}" class="text-dark text-decoration-none hover-primary">
                                                {{ $notification->title }}
                                            </a>
                                        @else
                                            {{ $notification->title }}
                                        @endif
                                        @if(!$notification->read_at)
                                            <span class="badge bg-danger ms-2" style="font-size: 0.65rem;">NEW</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1 text-muted">{{ $notification->message }}</p>
                            </div>

                             <div class="ms-3">
                                <div class="action-btns flex-column gap-2">
                                    <form action="{{ route('warehouse.notifications.destroy', $notification->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="mdi mdi-trash-can"></i>
                                        </button>
                                    </form>
                                    @if(!$notification->read_at)
                                    <button onclick="markAsRead({{ $notification->id }}); location.reload();" class="btn btn-sm btn-outline-primary" title="Mark as Read">
                                        <i class="mdi mdi-check"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="mdi mdi-bell-off-outline text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No notifications found.</p>
                    </div>
                @endforelse
            </div>
            
            @if($notifications->hasPages())
                <div class="card-footer bg-white">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>