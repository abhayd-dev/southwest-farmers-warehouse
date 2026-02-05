<x-app-layout title="Edit Department">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">

                @include('warehouse.partials.breadcrumb', [
                    'title' => 'Edit Department',
                    'items' => [
                        ['text' => 'Departments', 'url' => route('warehouse.departments.index')]
                    ]
                ])

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Edit Department</h5>
                    </div>
                    <div class="card-body">
                        {{-- Added needs-validation and novalidate --}}
                        <form action="{{ route('warehouse.departments.update', $department->id) }}" method="POST" class="needs-validation" novalidate>
                            @csrf @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Department Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ $department->name }}" class="form-control" required>
                                <div class="invalid-feedback">Please enter a department name.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Department Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" value="{{ $department->code }}" class="form-control" required>
                                <div class="invalid-feedback">Please enter a unique code.</div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('warehouse.departments.index') }}" class="btn btn-light border">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>