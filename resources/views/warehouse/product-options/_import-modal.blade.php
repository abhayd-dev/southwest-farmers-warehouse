<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('warehouse.product-options.import') }}" 
              enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf

            <div class="modal-content border-0 shadow">
                
                {{-- HEADER --}}
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-semibold" id="importModalLabel">
                        <i class="mdi mdi-upload me-2"></i>
                        Import Product Options
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body p-4">

                    {{-- INFO ALERT --}}
                    <div class="alert alert-info d-flex align-items-start mb-4 border-0">
                        <i class="mdi mdi-information-outline fs-5 me-2"></i>
                        <div class="small">
                            <strong>Important:</strong> Make sure your Excel file matches the sample format. 
                            Download the sample file below for reference.
                        </div>
                    </div>

                    {{-- CATEGORY --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Category <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-shape text-muted"></i>
                            </span>
                            <select name="category_id" id="categorySelect" class="form-select border-start-0" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>

                    {{-- SUBCATEGORY --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subcategory</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-tag text-muted"></i>
                            </span>
                            <select name="subcategory_id" id="subcategorySelect" class="form-select border-start-0">
                                <option value="">Select Subcategory (Optional)</option>
                            </select>
                        </div>
                    </div>

                    {{-- FILE UPLOAD --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Excel File <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-file-excel text-muted"></i>
                            </span>
                            <input type="file" name="file" class="form-control border-start-0" 
                                   accept=".xlsx,.xls" required>
                        </div>
                        <div class="form-text">Supported formats: .xlsx, .xls</div>
                        <div class="invalid-feedback">Please select a file to import.</div>
                    </div>

                    {{-- SAMPLE FILE DOWNLOAD --}}
                    <div class="p-3 bg-light rounded border">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm rounded bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3">
                                    <i class="mdi mdi-file-excel text-success fs-4"></i>
                                </div>
                                <div>
                                    <p class="mb-0 fw-semibold">Sample Template</p>
                                    <small class="text-muted">Download the sample file format</small>
                                </div>
                            </div>
                            <a href="{{ route('warehouse.product-options.sample') }}" 
                               class="btn btn-sm btn-outline-success">
                                <i class="mdi mdi-download me-1"></i>
                                Download
                            </a>
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-upload me-1"></i>
                        Import Data
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>