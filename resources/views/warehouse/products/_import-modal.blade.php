<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('warehouse.products.import') }}" enctype="multipart/form-data"
            class="needs-validation" novalidate>
            @csrf

            <div class="modal-content border-0 shadow">

                {{-- HEADER --}}
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-semibold" id="importModalLabel">
                        <i class="mdi mdi-upload me-2"></i>
                        Import Products
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body p-4">

                    <div class="alert alert-info d-flex align-items-start mb-4 border-0">
                        <i class="mdi mdi-information-outline fs-5 me-2"></i>
                        <div class="small">
                            <strong>Important:</strong>
                            Select Category and Subcategory here. They will be applied to all imported products.
                        </div>
                    </div>

                    {{-- CATEGORY --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Category <span class="text-danger">*</span>
                        </label>
                        <select name="category_id" id="categorySelectModal" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>

                    {{-- SUBCATEGORY --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Subcategory <span class="text-danger">*</span>
                        </label>
                        <select name="subcategory_id" id="subcategorySelectModal" class="form-select" required>
                            <option value="">Select Subcategory</option>
                        </select>
                        <div class="invalid-feedback">Please select a subcategory.</div>
                    </div>

                    {{-- FILE --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Excel File <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="invalid-feedback">Please select a valid Excel file.</div>
                    </div>

                    {{-- SAMPLE --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('warehouse.products.sample') }}" class="text-decoration-none small text-success">
                            <i class="mdi mdi-download"></i> Download Sample Format
                        </a>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-check-circle-outline me-1"></i> Import
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>