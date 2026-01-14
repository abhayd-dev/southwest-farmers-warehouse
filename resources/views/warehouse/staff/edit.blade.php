<x-app-layout title="Edit Staff">
    <div class="container-fluid">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('warehouse.staff.index') }}">Staff</a></li>
                <li class="breadcrumb-item active">Edit: {{ $user->name }}</li>
            </ol>
        </nav>

        <form method="POST" action="{{ route('warehouse.staff.update', $user->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf @method('PUT')
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center">
                            <img id="previewImg" src="{{ $user->avatar_url }}" class="rounded-circle border mb-3" width="120" height="120">
                            <input type="file" name="profile_image" class="form-control form-control-sm" onchange="previewFile(this)">
                            <small class="text-muted d-block mt-2">Leave empty to keep current image</small>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white fw-bold"><i class="mdi mdi-lock-reset me-1"></i> Login & Role</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ $userRole == $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-info text-white py-3">
                            <h5 class="mb-0 fw-bold">Update Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Employee ID</label>
                                    <input type="text" name="emp_code" class="form-control" value="{{ $user->emp_code }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Designation</label>
                                    <input type="text" name="designation" class="form-control" value="{{ $user->designation }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Address</label>
                                    <textarea name="address" class="form-control" rows="3">{{ $user->address }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-info text-white px-5 btn-lg">Update Staff</button>
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
                reader.onload = function(){ document.getElementById('previewImg').src = reader.result; }
                reader.readAsDataURL(file);
            }
        }
    </script>
    @endpush
</x-app-layout>