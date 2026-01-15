<x-app-layout title="Roles & Permissions">
    <div class="container-fluid">

        {{-- PAGE HEADER--}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-shield-account text-primary"></i> Roles & Permissions
                </h4>
                <small class="text-muted">Manage system access and user capabilities</small>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                {{-- Search Form --}}
                <form method="GET" action="{{ route('warehouse.roles.index') }}" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control" 
                               placeholder="Search Role Name..." 
                               style="max-width: 250px;">
                        
                        <button class="btn btn-primary" type="submit">
                            <i class="mdi mdi-magnify"></i>
                        </button>

                        @if(request('search'))
                            <a href="{{ route('warehouse.roles.index') }}" class="btn btn-outline-secondary" title="Clear">
                                <i class="mdi mdi-close"></i>
                            </a>
                        @endif
                    </div>
                </form>

                <a href="{{ route('warehouse.roles.create') }}" class="btn btn-success text-nowrap">
                    <i class="mdi mdi-plus-circle me-1"></i> Create New Role
                </a>
            </div>
        </div>

        {{-- ROLES LIST CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-muted"><i class="mdi mdi-format-list-bulleted"></i> Available Roles</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Sr. No.</th>
                            <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Role Name</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Guard</th>
                            <th class="py-3 text-center text-secondary small fw-bold text-uppercase">Permissions</th>
                            <th class="py-3 text-end px-4 text-secondary small fw-bold text-uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td class="px-4">
                                    {{ $loop->iteration + ($roles->currentPage() - 1) * $roles->perPage() }}
                                </td>
                                <td class="px-4">
                                    <div class="fw-bold text-dark">{{ $role->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                        {{ $role->guard_name }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3">
                                        {{ $role->permissions_count }} Permissions
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- Edit Button --}}
                                        <a href="{{ route('warehouse.roles.edit', $role->id) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Edit Role">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>

                                        {{-- Delete Button (Form) --}}
                                        @if($role->name !== 'Super Admin')
                                            <form action="{{ route('warehouse.roles.destroy', $role->id) }}" 
                                                  method="POST" 
                                                  class="d-inline delete-form" 
                                                  >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Role">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="System Role"><i class="mdi mdi-lock"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">No roles found matching your search.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $roles->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>