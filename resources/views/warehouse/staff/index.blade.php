<x-app-layout title="Warehouse Staff">
    <div class="container-fluid">

        {{-- PAGE HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-account-group text-primary"></i> Staff Management
                </h4>
                <small class="text-muted">Manage warehouse employees and roles</small>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Search Form (Visible to anyone with view_staff) --}}
                <form method="GET" action="{{ route('warehouse.staff.index') }}" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control" 
                               placeholder="Search..." 
                               style="max-width: 200px;">
                        
                        <select name="role_id" class="form-select" style="max-width: 150px;">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                    </div>
                </form>

                {{-- ADD BUTTON (Protected) --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_staff'))
                <a href="{{ route('warehouse.staff.create') }}" class="btn btn-success text-nowrap">
                    <i class="mdi mdi-account-plus me-1"></i> Add Staff
                </a>
                @endif
            </div>
        </div>

        {{-- STAFF TABLE --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-muted"><i class="mdi mdi-badge-account-horizontal"></i> Active Employees</h6>
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
                                        <img src="{{ $user->avatar_url }}" class="rounded-circle border" width="45" height="45" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <div class="small text-muted">ID: {{ $user->emp_code ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><i class="mdi mdi-email-outline me-1"></i> {{ $user->email }}</div>
                                    <div class="small text-muted"><i class="mdi mdi-phone-outline me-1"></i> {{ $user->phone ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary mb-1">
                                        {{ $user->roles->first()->name ?? 'No Role' }}
                                    </span>
                                    <div class="small text-muted">{{ $user->designation ?? 'Staff' }}</div>
                                </td>
                                <td class="text-center">
                                    {{-- Status Toggle (Protected) --}}
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input status-toggle" 
                                            type="checkbox" 
                                            role="switch"
                                            data-id="{{ $user->id }}"
                                            {{ $user->is_active ? 'checked' : '' }}
                                            {{-- Disable if self or no permission --}}
                                            {{ ($user->id === auth()->id() || !(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_staff'))) ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end px-4">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_staff'))
                                        <x-action-buttons 
                                            :editUrl="route('warehouse.staff.edit', $user->id)"
                                            :deleteUrl="$user->id !== auth()->id() ? route('warehouse.staff.destroy', $user->id) : null"
                                        />
                                    @else
                                        <span class="text-muted small">Read Only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4">No staff members found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top">{{ $staff->withQueryString()->links() }}</div>
        </div>
    </div>
    
    {{-- Scripts remain same, just the toggle logic is secured by backend too --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only attach listener to enabled inputs
            document.querySelectorAll('.status-toggle:not(:disabled)').forEach(function(toggle) {
                toggle.addEventListener('change', function(e) {
                    e.preventDefault();
                    const id = this.dataset.id;
                    const newStatus = this.checked ? 1 : 0;
                    const originalState = !this.checked;

                    Swal.fire({
                        title: 'Are you sure?', text: "Change user status?", icon: 'warning',
                        showCancelButton: true, confirmButtonText: 'Yes'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch("{{ route('warehouse.staff.status') }}", {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: id, status: newStatus })
                            }).then(res => res.json()).then(data => {
                                Swal.fire({ icon: 'success', title: data.message, toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                            }).catch(err => {
                                toggle.checked = originalState;
                                Swal.fire('Error', 'Failed to update status', 'error');
                            });
                        } else { toggle.checked = originalState; }
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>