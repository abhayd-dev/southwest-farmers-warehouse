<x-app-layout title="Products">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">

                    {{-- LEFT: BREADCRUMB & TITLE --}}
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.products.partials.breadcrumb', ['title' => 'Products'])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-cube-outline text-primary"></i>
                            Products Management
                        </h4>
                    </div>

                    {{-- RIGHT: SEARCH & ACTIONS --}}
                    <div class="flex-shrink-0">
                        @include('warehouse.products.partials.list-header-products')
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
                    <i class="mdi mdi-format-list-bulleted text-primary"></i>
                    Products List
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-semibold small">#</th>
                                <th class="py-3 text-muted fw-semibold small">ICON & PRODUCT NAME</th>
                                <th class="py-3 text-muted fw-semibold small">CATEGORY</th>
                                <th class="py-3 text-muted fw-semibold small">SUBCATEGORY</th>
                                <th class="py-3 text-muted fw-semibold small">SKU</th>
                                <th class="py-3 text-muted fw-semibold small">PRICE</th>
                                <th class="py-3 text-muted fw-semibold small text-center">STATUS</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($products as $product)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-muted">
                                        {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $product->icon ? Storage::url($product->icon) : 'https://placehold.co/50?text=IMG' }}"
                                                class="rounded bg-light border object-fit-cover shadow-sm"
                                                width="50" height="50" alt="{{ $product->product_name }}">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $product->product_name }}</div>
                                                <small class="text-muted">Option:
                                                    {{ $product->productOption->option_name ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            {{ $product->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                            {{ $product->subcategory->name ?? '-' }}
                                        </span>
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
                                                {{ $product->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('warehouse.products.edit', $product) }}"
                                                class="btn btn-outline-primary" data-bs-toggle="tooltip"
                                                title="Edit Product">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            <form method="POST"
                                                action="{{ route('warehouse.products.destroy', $product) }}"
                                                class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger delete-form"
                                                    data-bs-toggle="tooltip" title="Delete Product">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="mdi mdi-cube-outline text-muted" style="font-size: 4rem;"></i>
                                            <p class="text-muted mt-3 mb-0">No products found.</p>
                                            <a href="{{ route('warehouse.products.create') }}"
                                                class="btn btn-sm btn-primary mt-3">
                                                <i class="mdi mdi-plus"></i> Add First Product
                                            </a>
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

    {{-- IMPORT MODAL --}}
    @include('warehouse.products._import-modal')
    @include('warehouse.products._scripts')

</x-app-layout>
