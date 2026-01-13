<x-app-layout title="Create Category">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.categories.partials.breadcrumb', ['title' => 'Create Category'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-plus-circle text-success"></i> Create Category
                    </h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('warehouse.categories.store') }}" class="card border-0 shadow-sm needs-validation" novalidate>
                    @csrf
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Electronics">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g. CAT-ELEC">
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('warehouse.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Save Category</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-4">
                        <h6 class="fw-bold"><i class="mdi mdi-information-outline"></i> Tips</h6>
                        <ul class="mb-0 small ps-3">
                            <li>Use unique codes for better tracking.</li>
                            <li>Category names should be clear and concise.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>