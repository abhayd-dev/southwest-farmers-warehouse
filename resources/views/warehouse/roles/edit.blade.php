<x-app-layout title="Edit Role">
    <div class="container-fluid">
        <h4 class="fw-bold mb-4">Edit Role</h4>

        <form action="{{ route('warehouse.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Role Name -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <label class="form-label fw-bold">Role Name</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        required
                        value="{{ old('name', $role->name) }}"
                        placeholder="e.g. Area Manager"
                    >
                </div>
            </div>

            <!-- Permissions -->
            <div class="row">
                @foreach($permissions as $group => $perms)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-light fw-bold text-uppercase">
                                {{ $group }}
                            </div>

                            <div class="card-body">
                                @foreach($perms as $perm)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $perm->id }}"
                                            id="perm_{{ $perm->id }}"
                                            {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}
                                        >

                                        <label class="form-check-label" for="perm_{{ $perm->id }}">
                                            {{ str_replace('_', ' ', $perm->name) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Actions -->
            <div class="mt-3">
                <button class="btn btn-success btn-lg">
                    Update Role
                </button>

                <a href="{{ route('warehouse.roles.index') }}" class="btn btn-secondary btn-lg ms-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
