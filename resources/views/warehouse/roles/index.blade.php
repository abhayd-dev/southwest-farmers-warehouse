<x-app-layout title="Roles & Permissions">
    <div class="container-fluid">

        {{-- PAGE HEADER --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    {{-- Title --}}
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-shield-account text-primary me-2"></i> Roles & Permissions
                        </h4>
                        <small class="text-muted">Manage system access and user capabilities</small>
                    </div>
                    
                    {{-- Actions --}}
                    <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto justify-content-end">
                        {{-- Search Form --}}
                        <form method="GET" action="{{ route('warehouse.roles.index') }}" class="d-flex gap-2 w-60 w-md-auto">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       class="form-control border-start-0" 
                                       placeholder="Search Role Name..." 
                                       style="min-width: 200px;">
                                
                                <button class="btn btn-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                                
                                <a href="{{ route('warehouse.roles.index') }}" class="btn btn-outline-secondary" title="Reset Search">
                                    <i class="mdi mdi-refresh"></i>
                                </a>
                            </div>
                        </form>

                        {{-- Create Button --}}
                        <a href="{{ route('warehouse.roles.create') }}" class="btn btn-success shadow-sm text-nowrap">
                            <i class="mdi mdi-plus-circle me-1"></i> Create New Role
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROLES LIST CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="mdi mdi-format-list-bulleted me-2"></i> Available Roles List</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="px-4 py-3 small fw-bold text-uppercase">#</th>
                                <th class="py-3 small fw-bold text-uppercase">Role Name</th>
                                <th class="py-3 small fw-bold text-uppercase">Guard</th>
                                <th class="py-3 small fw-bold text-uppercase text-center">Permissions</th>
                                <th class="py-3 px-4 small fw-bold text-uppercase text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td class="px-4 text-muted">
                                        {{ $loop->iteration + ($roles->currentPage() - 1) * $roles->perPage() }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $role->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                            {{ $role->guard_name }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                            {{ $role->permissions_count }} Permissions
                                        </span>
                                    </td>
                                    <td class="px-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            {{-- Edit Button --}}
                                            <a href="{{ route('warehouse.roles.edit', $role->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               data-bs-toggle="tooltip" title="Edit Role">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            {{-- Delete Button (Protected) --}}
                                            @if($role->name !== 'Super Admin')
                                                <form action="{{ route('warehouse.roles.destroy', $role->id) }}" 
                                                      method="POST" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this role? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete Role">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-light text-muted border" disabled data-bs-toggle="tooltip" title="System Role Locked">
                                                    <i class="mdi mdi-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                            <i class="mdi mdi-shield-off opacity-25" style="font-size: 3rem;"></i>
                                            <p class="mt-2 mb-0">No roles found matching your search.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($roles->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $roles->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    @endpush
</x-app-layout>