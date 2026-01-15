<x-app-layout title="Warehouse Staff">
    <div class="container-fluid">

        {{-- PAGE HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 shadow-sm rounded">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="mdi mdi-account-group text-primary"></i> Staff Management
                </h4>
                <small class="text-muted">Manage warehouse employees and roles</small>
            </div>
            <div>
                <a href="{{ route('warehouse.staff.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-account-plus me-1"></i> Add New Staff
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
                                    @if ($user->is_active)
                                        <span class="badge bg-success-subtle text-success"><i
                                                class="mdi mdi-check-circle-outline"></i> Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
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
                                <td colspan="5" class="text-center py-4">No staff members found.. </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top">{{ $staff->links() }}</div>
        </div>
    </div>
</x-app-layout>
