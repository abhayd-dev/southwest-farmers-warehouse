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
                <form method="POST" action="{{ route('warehouse.subcategories.update', $subcategory) }}" enctype="multipart/form-data" class="card border-0 shadow-sm needs-validation" novalidate>
                    @csrf @method('PUT')
                    <div class="card-body p-4">
                        {{-- Icon --}}
                        <div class="mb-4 text-center">
                            <label for="iconInput" class="position-relative d-inline-block cursor-pointer">
                                <img id="iconPreview" src="{{ $subcategory->icon ? Storage::url($subcategory->icon) : asset('assets/images/placeholder.svg') }}" 
                                     class="rounded-circle border shadow-sm object-fit-cover" width="100" height="100">
                                <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm border">
                                    <i class="mdi mdi-pencil text-primary"></i>
                                </div>
                                <input type="file" name="icon" id="iconInput" class="d-none" accept="image/*" onchange="previewImage(event)">
                            </label>
                        </div>

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
    @push('scripts')
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){ document.getElementById('iconPreview').src = reader.result; };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
    @endpush
</x-app-layout>