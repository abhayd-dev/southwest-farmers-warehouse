<x-app-layout>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-info text-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold"><i class="mdi mdi-pencil-box me-2"></i> Edit Store Details</h4>
            <a href="{{ route('warehouse.stores.index') }}" class="btn btn-light btn-sm text-info fw-bold">
                <i class="mdi mdi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form action="{{ route('warehouse.stores.update', $store->id) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-primary mb-0"><i class="mdi mdi-store me-2"></i> Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="store_name" class="form-control form-control-lg" value="{{ old('store_name', $store->store_name) }}" required>
                                    <div class="invalid-feedback">Store name is required.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Store Email <span class="text-danger">*</span></label>
                                    <input type="email" name="store_email" class="form-control" value="{{ old('store_email', $store->email) }}" required>
                                    <div class="invalid-feedback">Valid email is required.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="store_phone" class="form-control" value="{{ old('store_phone', $store->phone) }}" required>
                                    <div class="invalid-feedback">Phone number is required.</div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Full Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" required>{{ old('address', $store->address) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', $store->city) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">State <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control" value="{{ old('state', $store->state) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $store->pincode) }}" required>
                                </div>

                                <div class="col-md-12">
                                    <hr class="text-muted my-3">
                                    <h6 class="text-muted mb-3"><i class="mdi mdi-map-marker-radius me-1"></i> Geographic Coordinates</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Latitude</label>
                                    <input type="text" name="latitude" class="form-control form-control-sm" value="{{ old('latitude', $store->latitude) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Longitude</label>
                                    <input type="text" name="longitude" class="form-control form-control-sm" value="{{ old('longitude', $store->longitude) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-dark mb-0"><i class="mdi mdi-toggle-switch me-2"></i> Store Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="isActiveSwitch" name="is_active" value="1" {{ $store->is_active ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold ms-2" for="isActiveSwitch">Store Active</label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Disabling this will prevent the store manager from logging in and stop stock requests.
                            </small>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4 bg-light">
                        <div class="card-header bg-transparent border-bottom-0 py-3">
                            <h5 class="card-title text-info mb-0"><i class="mdi mdi-account-tie me-2"></i> Assigned Manager</h5>
                        </div>
                        <div class="card-body pt-0">
                            @if($store->manager)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm bg-white text-info rounded-circle d-flex justify-content-center align-items-center shadow-sm me-3">
                                        <i class="mdi mdi-account fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $store->manager->name }}</h6>
                                        <small class="text-muted">{{ $store->manager->email }}</small>
                                    </div>
                                </div>
                                <div class="alert alert-warning border-0 shadow-sm py-2 px-3 small">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> To change the manager, please visit the <a href="{{ route('warehouse.staff.index') }}" class="alert-link">User Management</a> section.
                                </div>
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="mdi mdi-account-off fs-1 opacity-25"></i>
                                    <p class="mb-0">No Manager Assigned</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow">
                            <i class="mdi mdi-content-save me-2"></i> Update Store Details
                        </button>
                        <a href="{{ route('warehouse.stores.show', $store->id) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Bootstrap Validation Logic
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
    @endpush
</x-app-layout>