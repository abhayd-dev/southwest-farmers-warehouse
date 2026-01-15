<x-app-layout title="Warehouse Staff">
    <div class="container-fluid">

        {{-- PAGE HEADER & SEARCH BAR --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            
            {{-- Title --}}
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-account-group text-primary"></i> Staff Management
                </h4>
                <small class="text-muted">Manage warehouse employees and roles</small>
            </div>

            {{-- Actions: Search + Add --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                
                {{-- Search Form --}}
                <form method="GET" action="{{ route('warehouse.staff.index') }}" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control" 
                               placeholder="Search Name, Email, Code..." 
                               style="max-width: 200px;">
                        
                        <select name="role_id" class="form-select" style="max-width: 150px;">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button class="btn btn-primary" type="submit">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        
                        @if(request('search') || request('role_id'))
                            <a href="{{ route('warehouse.staff.index') }}" class="btn btn-outline-secondary" title="Clear">
                                <i class="mdi mdi-close"></i>
                            </a>
                        @endif
                    </div>
                </form>

                <a href="{{ route('warehouse.staff.create') }}" class="btn btn-success text-nowrap">
                    <i class="mdi mdi-account-plus me-1"></i> Add Staff
                </a>
            </div>
        </div>

        {{-- STAFF TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-muted"><i class="mdi mdi-badge-account-horizontal"></i> Active Employees
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Employee</th>
                            <th class="py-3">Contact</th>
                            <th class="py-3">Role & Designation</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $user)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-3">

                                        <img src="{{ $user->avatar_url }}" class="rounded-circle border" width="45"
                                            height="45" style="object-fit: cover;" alt="User Avatar">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <div class="small text-muted">ID: {{ $user->emp_code ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><i class="mdi mdi-email-outline me-1"></i> {{ $user->email }}
                                    </div>
                                    <div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i>
                                        {{ $user->phone ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary mb-1">
                                        {{ $user->roles->first()->name ?? 'No Role' }}
                                    </span>
                                    <div class="small text-muted">{{ $user->designation ?? 'Staff' }}</div>
                                </td>
                                <td class="text-center">
                                    {{-- STATUS TOGGLE SWITCH --}}
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input status-toggle" 
                                               type="checkbox" 
                                               role="switch"
                                               data-id="{{ $user->id }}"
                                               {{ $user->is_active ? 'checked' : '' }}
                                               {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end px-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('warehouse.staff.edit', $user->id) }}"
                                            class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('warehouse.staff.destroy', $user->id) }}"
                                                method="POST" class="d-inline delete-form" title="Delete Staff">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                                        class="mdi mdi-delete"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No staff members found matching your search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top">
                {{ $staff->withQueryString()->links() }}
            </div>
        </div>
    </div>

    {{-- SCRIPTS FOR STATUS TOGGLE --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.status-toggle').forEach(function(toggle) {
                toggle.addEventListener('change', function(e) {
                    const id = this.dataset.id;
                    const newStatus = this.checked ? 1 : 0;
                    const originalState = !this.checked; // To revert if failed

                    // Confirmation Dialog
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to change this user's status?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // API Request
                            fetch("{{ route('warehouse.staff.status') }}", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: id,
                                    status: newStatus
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    if(response.status === 403) throw new Error('Action not allowed');
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                }).fire({
                                    icon: 'success',
                                    title: data.message
                                });
                            })
                            .catch(error => {
                                // Revert toggle on error
                                toggle.checked = originalState;
                                Swal.fire(
                                    'Error!',
                                    error.message || 'Failed to update status.',
                                    'error'
                                );
                            });
                        } else {
                            // Revert toggle if cancelled
                            toggle.checked = originalState;
                        }
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>