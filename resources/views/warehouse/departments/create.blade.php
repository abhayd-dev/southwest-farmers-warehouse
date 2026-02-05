<x-app-layout title="Create Department">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                @include('warehouse.partials.breadcrumb', [
                    'title' => 'Create New',
                    'items' => [
                        ['text' => 'Departments', 'url' => route('warehouse.departments.index')]
                    ]
                ])

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Create New Department</h5>
                    </div>
                    <div class="card-body">
                        {{-- Added needs-validation and novalidate --}}
                        <form action="{{ route('warehouse.departments.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Department Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Frozen Foods">
                                <div class="invalid-feedback">Please enter a department name.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Department Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" required placeholder="e.g. FROZEN">
                                <div class="invalid-feedback">Please enter a unique code.</div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('warehouse.departments.index') }}" class="btn btn-light border">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>