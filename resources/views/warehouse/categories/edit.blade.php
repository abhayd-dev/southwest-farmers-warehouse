<x-app-layout title="Edit Category">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.categories.partials.breadcrumb', ['title' => 'Edit Category'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-pencil-circle text-warning"></i> Edit Category
                    </h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('warehouse.categories.update', $category) }}" class="card border-0 shadow-sm needs-validation" novalidate>
                    @csrf @method('PUT')
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" value="{{ $category->code }}" required>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('warehouse.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning text-white">Update Category</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>