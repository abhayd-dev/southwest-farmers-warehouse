<x-app-layout title="Edit Role">
    <div class="container-fluid">
        
        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('warehouse.roles.index') }}">Roles & Permissions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Role: {{ $role->name }}</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('warehouse.roles.update', $role->id) }}" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    
                    {{-- ROLE DETAILS CARD --}}
                    <div class="card border-0 shadow-lg mb-4">
                        <div class="card-header bg-info text-white py-3">
                            <h5 class="mb-0 fw-bold"><i class="mdi mdi-pencil-box me-2"></i> Edit Role Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $role->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PERMISSIONS GRID --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="mdi mdi-lock-open-check me-1"></i> Manage Permissions</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">Select All</button>
                    </div>
                    
                    <div class="row g-4">
                        @foreach($permissions as $group => $perms)
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                                    <div class="card-header bg-light fw-bold text-uppercase py-2 border-bottom d-flex justify-content-between">
                                        <span>{{ $group }}</span>
                                        <small class="text-muted">{{ count($perms) }} items</small>
                                    </div>
                                    <div class="card-body">
                                        @foreach($perms as $perm)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" 
                                                       value="{{ $perm->id }}" id="perm_{{ $perm->id }}"
                                                       {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label text-dark" for="perm_{{ $perm->id }}">
                                                    {{ ucwords(str_replace('_', ' ', $perm->name)) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ACTIONS --}}
                    <div class="d-flex justify-content-end gap-2 mt-4 mb-5">
                        <a href="{{ route('warehouse.roles.index') }}" class="btn btn-light border btn-lg">Cancel</a>
                        <button type="submit" class="btn btn-info text-white btn-lg px-5">
                            <i class="mdi mdi-content-save me-1"></i> Update Role
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    {{-- Validation & Helper Script --}}
    @push('scripts')
    <script>
        // Validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })();

        // Select All Button Logic
        document.getElementById('selectAllBtn').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.perm-checkbox');
            const allChecked = Array.from(checkboxes).every(c => c.checked);
            
            checkboxes.forEach(c => c.checked = !allChecked);
            this.textContent = allChecked ? 'Select All' : 'Deselect All';
        });
    </script>
    @endpush
</x-app-layout>