<x-app-layout title="Edit Subcategory">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.subcategories.partials.breadcrumb', ['title' => 'Edit Subcategory'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-pencil-circle text-warning"></i> Edit Subcategory
                    </h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('warehouse.subcategories.update', $subcategory) }}" class="card border-0 shadow-sm needs-validation" novalidate>
                    @csrf @method('PUT')
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parent Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Parent Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $subcategory->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subcategory Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $subcategory->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subcategory Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" value="{{ $subcategory->code }}" required>
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('warehouse.subcategories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning text-white">Update Subcategory</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>