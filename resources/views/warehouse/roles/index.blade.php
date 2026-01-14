<x-app-layout title="Roles & Permissions">
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-4">
            <h4 class="fw-bold">Manage Roles</h4>
            <a href="{{ route('warehouse.roles.create') }}" class="btn btn-primary">Create New Role</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Role Name</th>
                            <th>Permissions Count</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td class="px-4 fw-bold">{{ $role->name }}</td>
                            <td><span class="badge bg-info">{{ $role->permissions_count }}</span></td>
                            <td class="text-end px-4">
                                <a href="{{ route('warehouse.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('warehouse.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete Role?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
</x-app-layout>