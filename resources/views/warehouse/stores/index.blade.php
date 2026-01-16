<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="mdi mdi-store me-2"></i> All Stores
                </h4>
                <p class="text-muted mb-0 small mt-1">Manage physical store locations and managers</p>
            </div>
            <a href="{{ route('warehouse.stores.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Register New Store
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Search by name or code...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option value="">All Cities</option>
                            <option value="Lucknow">Lucknow</option>
                            <option value="Delhi">Delhi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-secondary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4">Store Details</th>
                                <th>Location</th>
                                <th>Manager</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stores as $store)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                            <i class="mdi mdi-store fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $store->store_name }}</h6>
                                            <small class="text-muted">{{ $store->store_code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium">{{ $store->city }}</span>
                                        <small class="text-muted">{{ Str::limit($store->address, 30) }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($store->manager)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs bg-info text-white rounded-circle me-2 d-flex justify-content-center align-items-center" style="width:30px;height:30px;">
                                                {{ substr($store->manager->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="d-block text-dark fw-medium small">{{ $store->manager->name }}</span>
                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $store->manager->phone }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-warning text-dark">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($store->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('warehouse.stores.show', $store->id) }}" class="btn btn-sm btn-outline-secondary" title="Dashboard">
                                            <i class="mdi mdi-chart-bar"></i>
                                        </a>
                                        <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete({{ $store->id }})">
                                            <i class="mdi mdi-trash-can"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $store->id }}" action="{{ route('warehouse.stores.destroy', $store->id) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <img src="{{ asset('assets/images/no-data.svg') }}" alt="No Data" height="100" class="mb-3 opacity-50">
                                    <p class="text-muted">No stores found. Register your first store!</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($stores->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $stores->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id) {
            if(confirm('Are you sure you want to delete this store? This action cannot be undone.')) {
                document.getElementById('delete-form-'+id).submit();
            }
        }
    </script>
    @endpush
</x-app-layout>