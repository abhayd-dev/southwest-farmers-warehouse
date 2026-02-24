<x-app-layout title="Add Staff">
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('warehouse.staff.index') }}">Staff</a></li>
                <li class="breadcrumb-item active">Add New Staff</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('warehouse.staff.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            
            <div class="row">
                {{-- LEFT COLUMN: Avatar & Login --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light fw-bold">Profile Image</div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img id="previewImg" src="{{ asset('assets/images/placeholder.svg') }}" class="rounded-circle border mb-3" width="120" height="120">
                                <input type="file" name="profile_image" class="form-control form-control-sm" onchange="previewFile(this)">
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white fw-bold"><i class="mdi mdi-lock me-1"></i> Login Details</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Select Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Personal Info --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="mb-0 fw-bold">Personal Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Employee ID (Code) <span class="text-danger">*</span></label>
                                    <input type="text" name="emp_code" class="form-control" required placeholder="e.g. EMP-001">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Designation</label>
                                    <input type="text" name="designation" class="form-control" placeholder="e.g. Senior Packer">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Residential Address</label>
                                    <textarea name="address" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-success px-5 btn-lg">Create Staff</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function previewFile(input) {
            var file = input.files[0];
            if(file){
                var reader = new FileReader();
                reader.onload = function(){
                    document.getElementById('previewImg').src = reader.result;
                }
                reader.readAsDataURL(file);
            }
        }
        // Validation Snippet
        (function () { 'use strict'; var forms = document.querySelectorAll('.needs-validation'); Array.prototype.slice.call(forms).forEach(function (form) { form.addEventListener('submit', function (event) { if (!form.checkValidity()) { event.preventDefault(); event.stopPropagation(); } form.classList.add('was-validated'); }, false); }); })();
    </script>
    @endpush
</x-app-layout>