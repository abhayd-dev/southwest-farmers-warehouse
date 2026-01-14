<x-app-layout title="Create Role">
    <div class="container-fluid">
        
        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('warehouse.roles.index') }}">Roles & Permissions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create New Role</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('warehouse.roles.store') }}" class="needs-validation" novalidate>
            @csrf

            <div class="row justify-content-center">
                <div class="col-lg-12">
                    
                    {{-- ROLE DETAILS CARD --}}
                    <div class="card border-0 shadow-lg mb-4">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0 fw-bold"><i class="mdi mdi-plus-box me-2"></i> Define New Role</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           placeholder="e.g. Area Manager" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text text-muted">Unique name for the role logic.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PERMISSIONS GRID --}}
                    <h5 class="fw-bold text-dark mb-3"><i class="mdi mdi-lock-open-check me-1"></i> Assign Permissions</h5>
                    
                    <div class="row g-4">
                        @foreach($permissions as $group => $perms)
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                                    <div class="card-header bg-light fw-bold text-uppercase py-2 border-bottom">
                                        {{ $group }}
                                    </div>
                                    <div class="card-body">
                                        @foreach($perms as $perm)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                       value="{{ $perm->id }}" id="perm_{{ $perm->id }}">
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
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="mdi mdi-check-circle me-1"></i> Save Role
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    {{-- Validation Script --}}
    @push('scripts')
    <script>
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