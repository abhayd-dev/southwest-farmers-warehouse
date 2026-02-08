<x-app-layout title="General Settings">
    <div class="container-fluid">

        {{-- Breadcrumb --}}
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb mb-0 bg-white p-3 rounded shadow-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark">
                                <i class="mdi mdi-home-outline me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('warehouse.settings.general') }}" class="text-decoration-none text-dark">
                                Settings
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">General Configuration</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form action="{{ route('warehouse.settings.update') }}" method="POST" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf

                    {{-- 1. BRANDING SECTION --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary">
                                <i class="mdi mdi-brush-variant fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">System Branding</h5>
                        </div>

                        <div class="card-body p-4">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3 border-bottom pb-2">Logos & Identity
                            </h6>
                            <div class="row g-4 mb-3">
                                {{-- Main Logo --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Main Logo</label>
                                    <div class="border p-3 rounded text-center bg-light position-relative">
                                        <img src="{{ isset($settings['main_logo']) ? Storage::url($settings['main_logo']) : 'https://placehold.co/150x50?text=Logo' }}"
                                            id="preview_main_logo" class="img-fluid mb-2" style="max-height: 50px;">
                                        <input type="file" name="main_logo" class="form-control form-control-sm mt-2"
                                            accept="image/png, image/jpeg"
                                            onchange="previewImage(this, 'preview_main_logo')">
                                    </div>
                                </div>

                                {{-- Login Logo --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Login Page Logo</label>
                                    <div class="border p-3 rounded text-center bg-light">
                                        <img src="{{ isset($settings['login_logo']) ? Storage::url($settings['login_logo']) : 'https://placehold.co/150x50?text=Login+Logo' }}"
                                            id="preview_login_logo" class="img-fluid mb-2" style="max-height: 60px;">
                                        <input type="file" name="login_logo"
                                            class="form-control form-control-sm mt-2" accept="image/png, image/jpeg"
                                            onchange="previewImage(this, 'preview_login_logo')">
                                    </div>
                                </div>

                                {{-- Favicon --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Favicon</label>
                                    <div class="border p-3 rounded text-center bg-light">
                                        <img src="{{ isset($settings['favicon']) ? Storage::url($settings['favicon']) : 'https://placehold.co/32x32?text=Icon' }}"
                                            id="preview_favicon" class="img-fluid mb-2"
                                            style="width: 32px; height: 32px;">
                                        <input type="file" name="favicon" class="form-control form-control-sm mt-2"
                                            accept="image/x-icon, image/png"
                                            onchange="previewImage(this, 'preview_favicon')">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">App Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="app_name" class="form-control"
                                        value="{{ $settings['app_name'] ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Support Email</label>
                                    <input type="email" name="support_email" class="form-control"
                                        value="{{ $settings['support_email'] ?? '' }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Address</label>
                                    <textarea name="app_address" class="form-control" rows="2">{{ $settings['app_address'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. AUTOMATION & NOTIFICATIONS SECTION (NEW) --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="bg-warning bg-opacity-10 p-2 rounded-circle text-warning">
                                <i class="mdi mdi-robot-outline fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">Automation & Alerts</h5>
                        </div>
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-uppercase text-muted small mb-3 border-bottom pb-2">Inventory
                                Thresholds</h6>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Low Stock Threshold</label>
                                    <div class="input-group">
                                        <input type="number" name="low_stock_threshold" class="form-control"
                                            value="{{ $settings['low_stock_threshold'] ?? 10 }}" min="1">
                                        <span class="input-group-text bg-light text-muted">Units</span>
                                    </div>
                                    <div class="form-text">System sends alerts when combined stock (Warehouse + Stores)
                                        falls below this number.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Alert Recipients</label>
                                    <input type="text" name="alert_emails" class="form-control"
                                        value="{{ $settings['alert_emails'] ?? '' }}"
                                        placeholder="admin@example.com, manager@example.com">
                                    <div class="form-text">Separate multiple email addresses with commas.</div>
                                </div>
                            </div>

                            <h6 class="fw-bold text-uppercase text-muted small mb-3 border-bottom pb-2">Email Triggers
                            </h6>
                            <div class="d-flex gap-5">
                                {{-- Low Stock Toggle --}}
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableLowStock"
                                        name="enable_low_stock_email" value="1"
                                        {{ isset($settings['enable_low_stock_email']) && $settings['enable_low_stock_email'] == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="enableLowStock">Enable Low Stock
                                        Emails</label>
                                    <div class="small text-muted">Send daily report at 9:00 AM</div>
                                </div>

                                {{-- Late PO Toggle --}}
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enableLatePO"
                                        name="enable_late_po_email" value="1"
                                        {{ isset($settings['enable_late_po_email']) && $settings['enable_late_po_email'] == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="enableLatePO">Enable Late Delivery
                                        Alerts</label>
                                    <div class="small text-muted">Notify when Vendor misses Expected Delivery Date
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white py-3 border-top">
                            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_settings'))
                                <div class="card border-0 shadow-sm mt-4">
                                    <div class="card-body text-end">
                                        <button type="submit" class="btn btn-success px-5 btn-lg">
                                            <i class="mdi mdi-content-save me-1"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mt-4">
                                    <i class="mdi mdi-lock"></i> You do not have permission to modify settings.
                                </div>
                                @endif
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Form Validation Logic
            (function() {
                'use strict'
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms)
                    .forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }
                            form.classList.add('was-validated')
                        }, false)
                    })
            })()

            // Image Preview Logic
            function previewImage(input, targetId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById(targetId).src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Success Alert
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: "{{ session('success') }}",
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif
        </script>
    @endpush
</x-app-layout>
