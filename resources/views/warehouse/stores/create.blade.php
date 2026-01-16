<x-app-layout  title="Add Store">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-success text-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold"><i class="mdi mdi-plus-box me-2"></i> Register New Store</h4>
            <a href="{{ route('warehouse.stores.index') }}" class="btn btn-light btn-sm text-success fw-bold">
                <i class="mdi mdi-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form action="{{ route('warehouse.stores.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            
            <div class="row">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-primary mb-0"><i class="mdi mdi-store me-2"></i> Store Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Store Name <span class="text-danger">*</span></label>
                                    <input type="text" name="store_name" class="form-control form-control-lg" placeholder="e.g. Southwest Farmers Lucknow" value="{{ old('store_name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Store Email</label>
                                    <input type="email" name="store_email" class="form-control" placeholder="store@example.com" value="{{ old('store_email') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="text" name="store_phone" class="form-control" placeholder="+91 98765..." value="{{ old('store_phone') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Full Address</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Building No, Street..." required>{{ old('address') }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">City</label>
                                    <input type="text" name="city" class="form-control" placeholder="City" value="{{ old('city') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">State</label>
                                    <input type="text" name="state" class="form-control" placeholder="State" value="{{ old('state') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Pincode</label>
                                    <input type="text" name="pincode" class="form-control" placeholder="123456" value="{{ old('pincode') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Latitude (Optional)</label>
                                    <input type="text" name="latitude" class="form-control form-control-sm" placeholder="e.g. 26.8467">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Longitude (Optional)</label>
                                    <input type="text" name="longitude" class="form-control form-control-sm" placeholder="e.g. 80.9462">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="card-title text-info mb-0"><i class="mdi mdi-account-tie me-2"></i> Manager Login</h5>
                        </div>
                        <div class="card-body bg-light bg-opacity-25">
                            <div class="alert alert-info border-0 shadow-sm mb-3">
                                <i class="mdi mdi-information-outline me-1"></i> A Super Admin account will be created for this store automatically.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Manager Name</label>
                                    <input type="text" name="manager_name" class="form-control" placeholder="Full Name" value="{{ old('manager_name') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Login Email</label>
                                    <input type="email" name="manager_email" class="form-control" placeholder="manager@example.com" value="{{ old('manager_email') }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Phone (Personal)</label>
                                    <input type="text" name="manager_phone" class="form-control" placeholder="Mobile" value="{{ old('manager_phone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg shadow">
                            <i class="mdi mdi-check-circle me-2"></i> Create Store & Account
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>