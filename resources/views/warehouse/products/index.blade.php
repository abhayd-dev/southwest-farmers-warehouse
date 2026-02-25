<x-app-layout title="Products">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.products.partials.breadcrumb', ['title' => 'Products'])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-cube-outline text-primary"></i> Products Management
                        </h4>
                    </div>
                    <div class="flex-shrink-0">
                        {{-- Only Show Header Actions if user has permission --}}
                        @if (auth()->user()->isSuperAdmin() ||
                                auth()->user()->hasPermission('create_products') ||
                                auth()->user()->hasPermission('manage_products'))
                            @include('warehouse.products.partials.list-header-products')
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-cube-outline text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Total Products</p>
                                <h4 class="mb-0 fw-bold text-danger">{{ $products->total() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="avatar-sm rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Active</p>
                                <h4 class="mb-0 fw-bold text-success">{{ $products->where('is_active', 1)->count() }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="avatar-sm rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-close-circle text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Inactive</p>
                                <h4 class="mb-0 fw-bold text-warning">{{ $products->where('is_active', 0)->count() }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="avatar-sm rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <i class="mdi mdi-view-list text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">This Page</p>
                                <h4 class="mb-0 fw-bold text-info">{{ $products->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="mdi mdi-format-list-bulleted text-primary"></i> Products List
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-semibold small">CODE</th>
                                <th class="py-3 text-muted fw-semibold small">UPC & BARCODE</th>
                                <th class="py-3 text-muted fw-semibold small">ICON & NAME</th>
                                <th class="py-3 text-muted fw-semibold small">CATEGORY</th>
                                <th class="py-3 text-muted fw-semibold small">SKU</th>
                                <th class="py-3 text-muted fw-semibold small">PRICE</th>
                                <th class="py-3 text-muted fw-semibold small text-center">STATUS</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($products as $product)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark border">{{ $product->id }}</span>
                                    </td>
                                    <td class="py-3">
                                        @if ($product->upc)
                                            <div class="fw-bold mb-1">{{ $product->upc }}</div>
                                        @endif
                                        @if ($product->barcode)
                                            <div class="d-flex flex-column align-items-start">
                                                <svg class="barcode-svg" data-code="{{ $product->barcode }}"></svg>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $product->icon ? Storage::url($product->icon) : asset('assets/images/placeholder.svg') }}"
                                                class="rounded bg-light border object-fit-cover shadow-sm"
                                                width="50" height="50" alt="{{ $product->product_name }}">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $product->product_name }}</div>
                                                <small
                                                    class="text-muted">{{ $product->department->name ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $product->category->name ?? '-' }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $product->subcategory->name ?? '-' }}</small>
                                    </td>
                                    <td class="py-3">
                                        <code class="bg-light px-2 py-1 rounded">{{ $product->sku ?? '-' }}</code>
                                    </td>
                                    <td class="py-3">
                                        @if ($product->price)
                                            <span
                                                class="fw-semibold text-success">${{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                                data-id="{{ $product->id }}"
                                                {{ $product->is_active ? 'checked' : '' }}
                                                {{ auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_products') ? '' : 'disabled' }}>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <x-action-buttons :editUrl="auth()->user()->isSuperAdmin() ||
                                        auth()->user()->hasPermission('edit_products')
                                            ? route('warehouse.products.edit', $product)
                                            : null" :deleteUrl="auth()->user()->isSuperAdmin() ||
                                        auth()->user()->hasPermission('delete_products')
                                            ? route('warehouse.products.destroy', $product)
                                            : null">
                                            @if (auth()->user()->isSuperAdmin() ||
                                                    auth()->user()->hasPermission('print_labels') ||
                                                    auth()->user()->hasPermission('view_products'))
                                                <a href="{{ route('warehouse.print.pallet', ['product_id' => $product->id, 'qty' => 1]) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-dark"
                                                    data-bs-toggle="tooltip" title="Print Label">
                                                    <i class="mdi mdi-printer"></i>
                                                </a>
                                            @endif
                                        </x-action-buttons>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="mdi mdi-cube-outline text-muted" style="font-size: 4rem;"></i>
                                            <p class="text-muted mt-3 mb-0">No products found.</p>
                                            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_products'))
                                                <a href="{{ route('warehouse.products.create') }}"
                                                    class="btn btn-sm btn-primary mt-3">
                                                    <i class="mdi mdi-plus"></i> Add First Product
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($products->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of
                            {{ $products->total() }} entries
                        </div>
                        <div>
                            {{ $products->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    {{-- MODALS --}}
    @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_products'))
        @include('warehouse.products._import-modal')
    @endif

    {{-- PRICING MODAL --}}
    @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_products'))
        <div class="modal fade" id="pricingModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning text-dark border-0">
                        <h5 class="modal-title fw-bold"><i class="mdi mdi-currency-usd"></i> Set Pricing (Bulk Update)
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('warehouse.products.bulk-price-update') }}" method="POST"
                        class="needs-validation" novalidate>
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-warning d-flex align-items-start small">
                                <i class="mdi mdi-alert me-2 fs-5"></i>
                                <div>
                                    This will <strong>increase</strong> the price of all products in the selected
                                    Category/Subcategory.
                                    <br> Example: Entering 10% will change $100 -> $110.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="priceCategorySelect" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Subcategory (Optional)</label>
                                <select name="subcategory_id" id="priceSubcategorySelect" class="form-select"
                                    disabled>
                                    <option value="">Select Subcategory</option>
                                </select>
                                <small class="text-muted">Leave empty to apply to entire Category.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Increase Percentage (%) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="percentage" class="form-control" min="0.01"
                                        step="0.01" placeholder="e.g. 10" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-light border"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark px-4">Update Prices</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('warehouse.products._scripts')

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render Barcodes in Table
            document.querySelectorAll('.barcode-svg').forEach(function(svg) {
                try {
                    JsBarcode(svg, svg.dataset.code, {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 1,
                        height: 25,
                        displayValue: false
                    });
                } catch (e) {
                    console.error("Barcode rendering error", e);
                }
            });

            // Handle Pricing Modal Subcategory Logic
            const catSelect = document.getElementById('priceCategorySelect');
            const subSelect = document.getElementById('priceSubcategorySelect');

            if (catSelect) {
                catSelect.addEventListener('change', function() {
                    const id = this.value;
                    subSelect.innerHTML = '<option value="">Loading...</option>';
                    subSelect.disabled = true;

                    if (id) {
                        fetch("{{ url('warehouse/fetch-subcategories') }}/" + id)
                            .then(res => res.json())
                            .then(data => {
                                subSelect.innerHTML =
                                    '<option value="">Select Subcategory (Optional)</option>';
                                data.forEach(sub => {
                                    subSelect.innerHTML +=
                                        `<option value="${sub.id}">${sub.name}</option>`;
                                });
                                subSelect.disabled = false;
                            });
                    } else {
                        subSelect.innerHTML = '<option value="">Select Category First</option>';
                    }
                });
            }
        });
    </script>

</x-app-layout>
