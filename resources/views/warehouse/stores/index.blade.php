<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="mb-0 text-primary fw-bold">
                    <i class="mdi mdi-store me-2"></i> All Stores
                </h4>
                <p class="text-muted mb-0 small mt-1">Manage physical store locations and managers</p>
            </div>
            <a href="{{ route('warehouse.stores.create') }}" class="btn btn-primary shadow-sm">
                <i class="mdi mdi-plus me-1"></i> Register New Store
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-magnify"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Search by name, code or city...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-dark w-100">Filter</button>
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
                                <th>Status</th> <th class="text-end pe-4">Actions</th>
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
                                        <span class="fw-medium text-dark">{{ $store->city }}</span>
                                        <small class="text-muted">{{ Str::limit($store->address, 25) }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($store->manager)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs bg-info text-white rounded-circle me-2 d-flex justify-content-center align-items-center" style="width:30px;height:30px;font-size:12px;">
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
                                    <div class="form-check form-switch form-switch-md">
                                        <input class="form-check-input status-toggle" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="status_{{ $store->id }}" 
                                               data-id="{{ $store->id }}"
                                               {{ $store->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_{{ $store->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('warehouse.stores.show', $store->id) }}" class="btn btn-sm btn-outline-secondary" title="View Dashboard">
                                            <i class="mdi mdi-chart-bar"></i>
                                        </a>
                                        <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Details">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Store" onclick="confirmDelete({{ $store->id }})">
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
                                    <div class="text-muted">
                                        <i class="mdi mdi-store-remove fs-1 opacity-25"></i>
                                        <p class="mt-2">No stores found. Click "Register New Store" to add one.</p>
                                    </div>
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
        $(document).ready(function() {
            
            // Status Toggle with SweetAlert
            $('.status-toggle').on('change', function(e) {
                let checkbox = $(this);
                let isChecked = checkbox.is(':checked');
                let storeId = checkbox.data('id');
                let newStatus = isChecked ? 1 : 0;

                Swal.fire({
                    title: 'Are you sure?',
                    text: isChecked ? "Do you want to Activate this store?" : "Do you want to Deactivate this store?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, change it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // AJAX Request
                        $.ajax({
                            url: "{{ route('warehouse.stores.update-status') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: storeId,
                                status: newStatus
                            },
                            success: function(response) {
                                if(response.success) {
                                    Swal.fire(
                                        'Updated!',
                                        response.message,
                                        'success'
                                    );
                                    
                                } else {
                                    Swal.fire('Error!', 'Something went wrong.', 'error');
                                    checkbox.prop('checked', !isChecked); // Revert
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Server Error. Please try again.', 'error');
                                checkbox.prop('checked', !isChecked); // Revert
                            }
                        });
                    } else {
                        checkbox.prop('checked', !isChecked);
                    }
                });
            });

            // Delete Confirmation
            window.confirmDelete = function(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this! All store data will be deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-'+id).submit();
                    }
                });
            };
        });
    </script>
    @endpush
</x-app-layout>