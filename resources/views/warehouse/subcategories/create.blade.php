<x-app-layout title="Create Subcategory">
    <div class="container-fluid">
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.subcategories.partials.breadcrumb', ['title' => 'Create Subcategory'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-plus-circle text-success"></i> Create Subcategory
                    </h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('warehouse.subcategories.store') }}" enctype="multipart/form-data" class="card border-0 shadow-sm needs-validation" novalidate>
                    @csrf
                    <div class="card-body p-4">
                        {{-- Icon --}}
                        <div class="mb-4 text-center">
                            <label for="iconInput" class="position-relative d-inline-block cursor-pointer">
                                <img id="iconPreview" src="https://placehold.co/100?text=Upload+Icon" 
                                     class="rounded-circle border shadow-sm object-fit-cover" width="100" height="100">
                                <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm border">
                                    <i class="mdi mdi-camera text-primary"></i>
                                </div>
                                <input type="file" name="icon" id="iconInput" class="d-none" accept="image/*" onchange="previewImage(event)">
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parent Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Parent Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subcategory Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Laptops">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subcategory Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g. SUB-LAP">
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('warehouse.subcategories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Save Subcategory</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body p-4">
                        <h6 class="fw-bold"><i class="mdi mdi-information-outline"></i> Hierarchy</h6>
                        <p class="small mb-0">Subcategories organize products within a main Category.</p>
                    </div>
                </div>
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