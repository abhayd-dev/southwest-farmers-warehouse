<x-app-layout title="Edit Vendor">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.vendors.partials.breadcrumb', ['title' => 'Edit Vendor'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-pencil-circle text-warning"></i> Edit Vendor: {{ $vendor->name }}
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.vendors.update', $vendor->id) }}" class="needs-validation" novalidate>
            @csrf @method('PUT')
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            
                            {{-- Basic Info --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h6 class="fw-bold text-primary mb-3">Company Details</h6>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $vendor->name }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <textarea name="address" class="form-control" rows="2">{{ $vendor->address }}</textarea>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Contact Info --}}
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <h6 class="fw-bold text-info mb-3">Contact Person</h6>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Contact Name</label>
                                    <input type="text" name="contact_person" class="form-control" value="{{ $vendor->contact_person }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $vendor->email }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $vendor->phone }}">
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Operational Info --}}
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <h6 class="fw-bold text-warning mb-3">Operations</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Lead Time (Days)</label>
                                    <div class="input-group">
                                        <input type="number" name="lead_time_days" class="form-control" value="{{ $vendor->lead_time_days }}" min="0">
                                        <span class="input-group-text bg-light">Days</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="is_active" class="form-select">
                                        <option value="1" {{ $vendor->is_active ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !$vendor->is_active ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('warehouse.vendors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-warning text-white px-4">Update Vendor</button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>