<div class="modal fade" id="importSubCategoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('warehouse.subcategories.import') }}" enctype="multipart/form-data" class="modal-content border-0 shadow needs-validation" novalidate>
            @csrf
            
            {{-- HEADER --}}
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-semibold"><i class="mdi mdi-upload me-2"></i> Import Subcategories</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-4">
                
                {{-- INFO --}}
                <div class="alert alert-info d-flex align-items-start mb-3 border-0">
                    <i class="mdi mdi-information-outline fs-5 me-2"></i>
                    <div class="small">
                        <strong>Note:</strong> Select a Category below. All subcategories in the file will be assigned to it.
                    </div>
                </div>

                {{-- CATEGORY SELECTION --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Choose Parent Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- FILE INPUT --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.csv" required>
                </div>
                
                <div class="alert alert-light small border border-light">
                    <i class="mdi mdi-file-excel me-1 text-success"></i> 
                    <strong>Columns needed:</strong> <code>name</code>, <code>code</code>
                </div>

                <div class="text-end">
                    <a href="{{ route('warehouse.subcategories.sample') }}" class="small text-success text-decoration-none">
                        <i class="mdi mdi-download"></i> Download Sample
                    </a>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Import</button>
            </div>
        </form>
    </div>
</div>