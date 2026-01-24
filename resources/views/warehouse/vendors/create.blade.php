<x-app-layout title="Create Vendor">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.vendors.partials.breadcrumb', ['title' => 'Create Vendor'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-plus-circle text-success"></i> Add New Vendor
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.vendors.store') }}" class="needs-validation" novalidate>
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            
                            {{-- Basic Info --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-card-account-details-outline"></i> Company Details</h6>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. Metro Supplies Ltd.">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Full Billing Address"></textarea>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Contact Info --}}
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <h6 class="fw-bold text-info mb-3"><i class="mdi mdi-contacts-outline"></i> Contact Person</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Contact Name</label>
                                    <input type="text" name="contact_person" class="form-control" placeholder="e.g. John Doe">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="vendor@example.com">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Operational Info --}}
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <h6 class="fw-bold text-warning mb-3"><i class="mdi mdi-clock-outline"></i> Operations</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Lead Time (Days)</label>
                                    <div class="input-group">
                                        <input type="number" name="lead_time_days" class="form-control" value="0" min="0">
                                        <span class="input-group-text bg-light">Days</span>
                                    </div>
                                    <small class="text-muted">Average time to deliver after PO is sent.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('warehouse.vendors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-success px-4">Save Vendor</button>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Sidebar Tips --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><i class="mdi mdi-information-outline"></i> Why Lead Time?</h6>
                            <p class="small mb-0">Setting an accurate <strong>Lead Time</strong> helps the system calculate Reorder Points accurately. If a vendor takes 5 days to deliver, the system will alert you 5 days before stock runs out.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>