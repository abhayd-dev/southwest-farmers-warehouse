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
                            <a href="{{ route('warehouse.settings.general') }}"
                                class="text-decoration-none text-dark">
                                Settings
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">General</li> 

                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <form action="{{ route('warehouse.settings.update') }}" method="POST" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary">
                                <i class="mdi mdi-cog-outline fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-bold text-dark">System Branding & Contact</h5>
                        </div>

                        <div class="card-body p-4">

                            {{-- Section 1: Branding Images --}}
                            <h6 class="fw-bold text-uppercase text-muted small mb-3 border-bottom pb-2">Logos & Icons
                            </h6>
                            <div class="row g-4 mb-5">
                                {{-- Main Logo --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Main Logo
                                        (Navbar/Reports)</label>
                                    <div class="border p-3 rounded text-center bg-light position-relative">
                                        @if (isset($settings['main_logo']))
                                            <img src="{{ Storage::url($settings['main_logo']) }}"
                                                id="preview_main_logo" class="img-fluid mb-2" style="max-height: 50px;">
                                        @else
                                            <img src="https://placehold.co/150x50?text=Logo" id="preview_main_logo"
                                                class="img-fluid mb-2" style="max-height: 50px;">
                                        @endif
                                        <input type="file" name="main_logo" class="form-control form-control-sm mt-2"
                                            accept="image/png, image/jpeg"
                                            onchange="previewImage(this, 'preview_main_logo')">
                                        <div class="form-text small">Recommended: 150x50px PNG</div>
                                    </div>
                                </div>

                                {{-- Login Logo --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Login Page Logo</label>
                                    <div class="border p-3 rounded text-center bg-light">
                                        @if (isset($settings['login_logo']))
                                            <img src="{{ Storage::url($settings['login_logo']) }}"
                                                id="preview_login_logo" class="img-fluid mb-2"
                                                style="max-height: 60px;">
                                        @else
                                            <img src="https://placehold.co/150x50?text=Login+Logo"
                                                id="preview_login_logo" class="img-fluid mb-2"
                                                style="max-height: 60px;">
                                        @endif
                                        <input type="file" name="login_logo"
                                            class="form-control form-control-sm mt-2" accept="image/png, image/jpeg"
                                            onchange="previewImage(this, 'preview_login_logo')">
                                        <div class="form-text small">Larger logo for login screen</div>
                                    </div>
                                </div>

                                {{-- Favicon --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Favicon (Browser Tab)</label>
                                    <div class="border p-3 rounded text-center bg-light">
                                        @if (isset($settings['favicon']))
                                            <img src="{{ Storage::url($settings['favicon']) }}" id="preview_favicon"
                                                class="img-fluid mb-2" style="width: 32px; height: 32px;">
                                        @else
                                            <img src="https://placehold.co/32x32?text=Ico" id="preview_favicon"
                                                class="img-fluid mb-2" style="width: 32px; height: 32px;">
                                        @endif
                                        <input type="file" name="favicon" class="form-control form-control-sm mt-2"
                                            accept="image/x-icon, image/png"
                                            onchange="previewImage(this, 'preview_favicon')">
                                        <div class="form-text small">Recommended: 32x32px ICO/PNG</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section 2: General Info --}}
                            <h6 class="fw-bold text-uppercase text-muted small mb-3 border-bottom pb-2">App Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">App Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="app_name" class="form-control"
                                        value="{{ $settings['app_name'] ?? '' }}" required>
                                    <div class="invalid-feedback">App Name is required.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">App Phone</label>
                                    <input type="text" name="app_phone" class="form-control"
                                        value="{{ $settings['app_phone'] ?? '' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Support Email</label>
                                    <input type="email" name="support_email" class="form-control"
                                        value="{{ $settings['support_email'] ?? '' }}">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Address</label>
                                    <textarea name="app_address" class="form-control" rows="3">{{ $settings['app_address'] ?? '' }}</textarea>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer bg-white py-3 border-top">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="mdi mdi-content-save-outline me-1"></i> Save Settings
                                </button>
                            </div>
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
