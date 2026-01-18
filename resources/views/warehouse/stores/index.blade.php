<x-app-layout title="All Stores">
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
                <form action="{{ route('warehouse.stores.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <select name="city" class="form-select">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="mdi mdi-filter me-1"></i> Filter
                        </button>
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
                                    <div class="form-check form-switch">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                            id="status_{{ $store->id }}" 
                                            data-id="{{ $store->id }}" 
                                            {{ $store->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_{{ $store->id }}">
                                            <span class="status-text badge {{ $store->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                {{ $store->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </label>
                                    </div>
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
                                    <p class="text-muted">No stores found matching your criteria.</p>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            window.confirmDelete = function(id) {
                Swal.fire({
                    title: 'Delete Store?',
                    text: "This action cannot be undone!",
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

            const toggles = document.querySelectorAll('.status-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const storeId = this.getAttribute('data-id');
                    const isChecked = this.checked;
                    const newStatus = isChecked ? 1 : 0;
                    
                    this.checked = !isChecked; 

                    Swal.fire({
                        title: isChecked ? 'Activate Store?' : 'Deactivate Store?',
                        text: isChecked ? "Store manager will be able to login." : "Store access will be revoked!",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.checked = isChecked;
                            updateStatusLabel(this, isChecked);

                            fetch("{{ route('warehouse.stores.update-status') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    id: storeId,
                                    status: newStatus
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if(data.success) {
                                    Swal.fire({
                                        title: 'Updated!',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                } else {
                                    this.checked = !isChecked;
                                    updateStatusLabel(this, !isChecked);
                                    Swal.fire('Error!', 'Something went wrong.', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.checked = !isChecked;
                                updateStatusLabel(this, !isChecked);
                                Swal.fire('Error!', 'Network error.', 'error');
                            });
                        }
                    });
                });
            });

            function updateStatusLabel(checkbox, isChecked) {
                const labelSpan = checkbox.nextElementSibling.querySelector('.status-text');
                if(isChecked) {
                    labelSpan.textContent = 'Active';
                    labelSpan.className = 'status-text badge bg-success-subtle text-success';
                } else {
                    labelSpan.textContent = 'Inactive';
                    labelSpan.className = 'status-text badge bg-danger-subtle text-danger';
                }
            }
        });
    </script>
    @endpush
</x-app-layout>