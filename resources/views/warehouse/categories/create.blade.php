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
                {{-- Added enctype --}}
                <form method="POST" action="{{ route('warehouse.categories.store') }}" enctype="multipart/form-data" class="card border-0 shadow-sm needs-validation" novalidate>
                    @csrf
                    <div class="card-body p-4">
                        
                        {{-- Icon Upload --}}
                        <div class="mb-4 text-center">
                            <label for="iconInput" class="position-relative d-inline-block cursor-pointer">
                                <img id="iconPreview" src="https://placehold.co/100?text=Upload+Icon" 
                                     class="rounded-circle border shadow-sm object-fit-cover" 
                                     width="100" height="100" alt="Icon Preview">
                                <div class="position-absolute bottom-0 end-0 bg-white rounded-circle p-1 shadow-sm border">
                                    <i class="mdi mdi-camera text-primary"></i>
                                </div>
                                <input type="file" name="icon" id="iconInput" class="d-none" accept="image/*" onchange="previewImage(event)">
                            </label>
                            <div class="small text-muted mt-2">Click to upload icon</div>
                        </div>

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
                            <li>Upload clear icons for better visibility.</li>
                            <li>Use unique codes for better tracking.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('iconPreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
    @endpush
</x-app-layout>