<x-app-layout title="Edit Warehouse">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                    <i class="mdi mdi-home-outline"></i> Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('warehouse.index') }}" class="text-decoration-none">
                                    Warehouse
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Edit Warehouse
                            </li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-pencil-circle text-warning"></i>
                        Edit Warehouse
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.update', $warehouse) }}" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="row">
                {{-- MAIN FORM --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="mdi mdi-information text-primary"></i>
                                Basic Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">

                                {{-- WAREHOUSE NAME --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Warehouse Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-warehouse text-muted"></i>
                                        </span>
                                        <input type="text" name="warehouse_name" class="form-control border-start-0" 
                                               value="{{ old('warehouse_name', $warehouse->warehouse_name) }}"
                                               placeholder="Enter warehouse name" required>
                                    </div>
                                    <div class="invalid-feedback">Please enter a warehouse name.</div>
                                </div>

                                {{-- CODE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Code <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-barcode text-muted"></i>
                                        </span>
                                        <input type="text" name="code" class="form-control border-start-0" 
                                               value="{{ old('code', $warehouse->code) }}"
                                               placeholder="Enter warehouse code" required>
                                    </div>
                                    <div class="invalid-feedback">Code is required.</div>
                                </div>

                                {{-- EMAIL --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-email-outline text-muted"></i>
                                        </span>
                                        <input type="email" name="email" class="form-control border-start-0" 
                                               value="{{ old('email', $warehouse->email) }}"
                                               placeholder="warehouse@example.com">
                                    </div>
                                </div>

                                {{-- PHONE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-phone-outline text-muted"></i>
                                        </span>
                                        <input type="text" name="phone" class="form-control border-start-0" 
                                               value="{{ old('phone', $warehouse->phone) }}"
                                               placeholder="+1 234 567 8900">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- LOCATION INFORMATION --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="mdi mdi-map-marker text-danger"></i>
                                Location Details
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">

                                {{-- ADDRESS --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 align-items-start pt-2">
                                            <i class="mdi mdi-map-marker text-muted"></i>
                                        </span>
                                        <textarea name="address" class="form-control border-start-0" 
                                                  rows="3" placeholder="Enter complete address">{{ old('address', $warehouse->address) }}</textarea>
                                    </div>
                                </div>

                                {{-- CITY --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-city text-muted"></i>
                                        </span>
                                        <input type="text" name="city" class="form-control border-start-0" 
                                               value="{{ old('city', $warehouse->city) }}"
                                               placeholder="Enter city">
                                    </div>
                                </div>

                                {{-- STATE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">State</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-map text-muted"></i>
                                        </span>
                                        <input type="text" name="state" class="form-control border-start-0" 
                                               value="{{ old('state', $warehouse->state) }}"
                                               placeholder="Enter state">
                                    </div>
                                </div>

                                {{-- COUNTRY --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-earth text-muted"></i>
                                        </span>
                                        <input type="text" name="country" class="form-control border-start-0" 
                                               value="{{ old('country', $warehouse->country) }}"
                                               placeholder="Enter country">
                                    </div>
                                </div>

                                {{-- PINCODE --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Pincode</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="mdi mdi-mailbox text-muted"></i>
                                        </span>
                                        <input type="text" name="pincode" class="form-control border-start-0" 
                                               value="{{ old('pincode', $warehouse->pincode) }}"
                                               placeholder="Enter pincode">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <div class="col-lg-4">
                    {{-- WAREHOUSE INFO --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="mdi mdi-warehouse"></i>
                                Warehouse Details
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Status:</small>
                                    <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'warning' }}">
                                        {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Created:</small>
                                    <small class="fw-semibold">{{ $warehouse->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Last Updated:</small>
                                    <small class="fw-semibold">{{ $warehouse->updated_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- GUIDELINES --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="mdi mdi-information-outline"></i>
                                Guidelines
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-check-circle text-success"></i>
                                    <small class="text-muted">Update warehouse name if location changes</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-check-circle text-success"></i>
                                    <small class="text-muted">Keep contact information up to date</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-check-circle text-success"></i>
                                    <small class="text-muted">Verify address for accurate shipping</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS CARD --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg text-white">
                                    <i class="mdi mdi-content-save me-1"></i>
                                    Update Warehouse
                                </button>
                                <a href="{{ route('warehouse.index') }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="mdi mdi-arrow-left me-1"></i>
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

</x-app-layout>