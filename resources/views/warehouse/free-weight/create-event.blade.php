<x-app-layout title="Start Packaging Event">
    <div class="container-fluid p-3 p-md-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="{{ route('warehouse.free-weight.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="fw-bold mb-0">Start Packaging Event</h4>
                        <small class="text-muted">Convert bulk weight into discrete packages</small>
                    </div>
                </div>

                {{-- Bulk Stock Info Card --}}
                <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
                    <i class="mdi mdi-scale mdi-36px text-info"></i>
                    <div>
                        <div class="fw-bold fs-5">{{ $bulkProduct->product->product_name ?? 'N/A' }}</div>
                        <div>
                            Available Bulk Weight:
                            <strong class="{{ $bulkProduct->bulk_weight < 100 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($bulkProduct->bulk_weight, 2) }} {{ $bulkProduct->unit }}
                            </strong>
                        </div>
                    </div>
                </div>

                @if($bulkProduct->packages->isEmpty())
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert me-2"></i>
                        No package sizes defined for this product.
                        <a href="{{ route('warehouse.free-weight.package.create', $bulkProduct->id) }}" class="alert-link">Add one first.</a>
                    </div>
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ route('warehouse.free-weight.event.store', $bulkProduct->id) }}" method="POST" class="needs-validation" novalidate>
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Select Package Size <span class="text-danger">*</span></label>
                                    <div class="row g-3">
                                        @foreach($bulkProduct->packages as $pkg)
                                            <div class="col-12 col-sm-6">
                                                <label class="d-block border rounded p-3 cursor-pointer package-option" style="cursor:pointer;">
                                                    <input type="radio" name="package_id" value="{{ $pkg->id }}"
                                                           class="form-check-input me-2" required>
                                                    <strong>{{ $pkg->package_name }}</strong>
                                                    <div class="small text-muted mt-1">
                                                        {{ $pkg->package_size }} {{ $pkg->unit }} per pack
                                                        @if($pkg->targetProduct)
                                                            &rarr; <span class="text-success">{{ $pkg->targetProduct->product_name }}</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Number of Packages to Create <span class="text-danger">*</span></label>
                                    <input type="number" name="packages_to_create" id="packagesToCreate"
                                           class="form-control form-control-lg" min="1"
                                           value="{{ old('packages_to_create') }}" placeholder="e.g. 50" required>
                                    <div class="form-text" id="weightPreview"></div>
                                    <div class="invalid-feedback">Please enter a valid quantity.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"
                                              placeholder="Optional notes about this packaging run...">{{ old('notes') }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('warehouse.free-weight.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="mdi mdi-play me-1"></i> Execute Packaging
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const packages = @json($bulkProduct->packages->keyBy('id'));
        const availableWeight = {{ $bulkProduct->bulk_weight }};

        document.querySelectorAll('input[name="package_id"]').forEach(radio => {
            radio.addEventListener('change', updatePreview);
        });
        document.getElementById('packagesToCreate')?.addEventListener('input', updatePreview);

        function updatePreview() {
            const selectedPkg = document.querySelector('input[name="package_id"]:checked');
            const qty = parseInt(document.getElementById('packagesToCreate').value) || 0;
            const preview = document.getElementById('weightPreview');

            if (!selectedPkg || qty <= 0) {
                preview.textContent = '';
                return;
            }

            const pkg = packages[selectedPkg.value];
            const required = pkg.package_size * qty;
            const remaining = availableWeight - required;

            if (required > availableWeight) {
                preview.innerHTML = `<span class="text-danger fw-bold">⚠ Requires ${required.toFixed(2)} ${pkg.unit} — exceeds available ${availableWeight.toFixed(2)} ${pkg.unit}</span>`;
            } else {
                preview.innerHTML = `Will use <strong>${required.toFixed(2)} ${pkg.unit}</strong>. Remaining bulk: <strong>${remaining.toFixed(2)} ${pkg.unit}</strong>`;
            }
        }
    </script>
    @endpush
</x-app-layout>
