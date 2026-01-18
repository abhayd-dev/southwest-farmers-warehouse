<x-app-layout title="Store Details - {{ $store->store_name }}">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stores.index') }}">Stores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $store->store_code }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">{{ $store->store_name }} <span class="badge bg-success fs-6 align-middle ms-2">Active</span></h4>
                <p class="text-muted mb-0"><i class="mdi mdi-map-marker me-1"></i> {{ $store->address }}, {{ $store->city }}</p>
            </div>
            <div>
                <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-primary shadow-sm">
                    <i class="mdi mdi-pencil me-1"></i> Edit Details
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Inventory Value</h6>
                        <h3 class="mb-0 fw-bold">${{ number_format($analytics['inventory_value']) }}</h3>
                        <small class="text-success"><i class="mdi mdi-trending-up me-1"></i>Current Stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Total Products</h6>
                        <h3 class="mb-0 fw-bold">{{ $analytics['inventory_items'] }}</h3>
                        <small class="text-muted">Unique SKUs in stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Low Stock Alerts</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $analytics['low_stock_count'] }}</h3>
                        <small class="text-danger fw-bold"><i class="mdi mdi-alert-circle me-1"></i>Action Needed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Total Staff</h6>
                        <h3 class="mb-0 fw-bold">{{ $analytics['staff_count'] }}</h3>
                        <small class="text-muted">Active Users</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Sales & Stock Analytics</h5>
                            <small class="text-muted">Weekly Performance</small>
                        </div>
                    </div>
                    <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 300px;">
                        <div class="text-center text-muted">
                            <i class="mdi mdi-chart-bar fs-1 opacity-25"></i>
                            <p class="mt-2">Analytics Chart Placeholder</p>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="mdi mdi-account-group me-2 text-primary"></i>Store Staff & Users</h5>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                            <i class="mdi mdi-plus me-1"></i> Add New Staff
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="ps-4">Name</th>
                                        <th>Role</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staffMembers as $staff)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold">
                                                    {{ substr($staff->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $staff->name }}</h6>
                                                    <small class="text-muted">{{ $staff->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">
                                                {{ $staff->role_name }}
                                            </span>
                                        </td>
                                        <td>{{ $staff->phone ?? 'N/A' }}</td>
                                        <td>
                                            @if($staff->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if(!$staff->isStoreAdmin())
                                            <form action="{{ route('warehouse.stores.staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('Remove this user?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                    <i class="mdi mdi-trash-can fs-5"></i>
                                                </button>
                                            </form>
                                            @else
                                            <span class="text-muted small"><i class="mdi mdi-shield-check me-1"></i>Main Admin</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No staff members found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="mdi mdi-map-marker-outline me-2 text-primary"></i>Location Details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Code</span>
                                <span class="fw-bold">{{ $store->store_code }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">City</span>
                                <span class="fw-bold">{{ $store->city }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Pincode</span>
                                <span class="fw-bold">{{ $store->pincode }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Created</span>
                                <span class="fw-bold">{{ $store->created_at->format('d M, Y') }}</span>
                            </li>
                        </ul>
                        @if($store->latitude)
                        <div class="mt-3 bg-light rounded p-3 text-center">
                            <small class="text-muted d-block mb-1">Coordinates</small>
                            <code>{{ $store->latitude }}, {{ $store->longitude }}</code>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="mdi mdi-chart-box-outline me-2 text-info"></i>Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Avg Rating</small>
                                    <h6 class="mb-0 fw-bold">4.8<span class="text-warning fs-6">★</span></h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Performance</small>
                                    <h6 class="mb-0 fw-bold text-success">95%</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="mdi mdi-account-plus me-2"></i>Add New Staff</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('warehouse.stores.staff.store', $store->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Assign Role</label>
                            <select name="role_name" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>