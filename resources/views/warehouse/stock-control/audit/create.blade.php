<x-app-layout title="Start Stock Audit">
    <div class="container-fluid">
        
        @include('warehouse.partials.breadcrumb', [
            'title' => 'Initiate Audit',
            'items' => [
                ['text' => 'Stock Audits', 'url' => route('warehouse.stock-control.audit.index')],
                ['text' => 'New Session']
            ]
        ])

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="mdi mdi-clipboard-text-outline me-2"></i>Audit Setup</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('warehouse.stock-control.audit.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-start" role="alert">
                                <i class="mdi mdi-information-outline fs-4 me-2"></i>
                                <div>
                                    <strong>Snapshot Warning:</strong> Starting an audit will "freeze" the system quantity for comparison. Ensure no major movements are happening.
                                </div>
                            </div>

                            {{-- Audit Type Selection --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Audit Scope</label>
                                <select name="type" id="auditType" class="form-select bg-light" required>
                                    <option value="full">Full Warehouse Inventory</option>
                                    <option value="department">By Department</option>
                                </select>
                            </div>

                            {{-- Department Select (Hidden by default) --}}
                            <div class="mb-3" id="departmentDiv" style="display: none;">
                                <label class="form-label fw-bold">Select Department <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select">
                                    <option value="">-- Choose Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted">Only products in this department will be included in the audit.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Notes / Remarks</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="E.g. Monthly Cycle Count for Jan 2026..."></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('warehouse.stock-control.audit.index') }}" class="btn btn-light border">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-play me-1"></i> Start Audit Session
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const auditType = document.getElementById('auditType');
            const deptDiv = document.getElementById('departmentDiv');
            const deptSelect = deptDiv.querySelector('select');

            // Handle Type Change
            auditType.addEventListener('change', function() {
                if (this.value === 'department') {
                    deptDiv.style.display = 'block';
                    deptSelect.setAttribute('required', 'required');
                } else {
                    deptDiv.style.display = 'none';
                    deptSelect.removeAttribute('required');
                    deptSelect.value = '';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>