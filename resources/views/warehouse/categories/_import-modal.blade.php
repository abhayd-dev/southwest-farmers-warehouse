<div class="modal fade" id="importCategoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('warehouse.categories.import') }}" enctype="multipart/form-data" class="modal-content border-0 shadow needs-validation" novalidate>
            @csrf
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-semibold"><i class="mdi mdi-upload me-2"></i> Import Categories</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.csv" required>
                </div>
                <div class="text-end">
                    <a href="{{ route('warehouse.categories.sample') }}" class="small text-success text-decoration-none"><i class="mdi mdi-download"></i> Download Sample</a>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Import</button>
            </div>
        </form>
    </div>
</div>