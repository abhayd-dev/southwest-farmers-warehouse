<x-app-layout title="Product Options">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">

                    {{-- LEFT: BREADCRUMB & TITLE --}}
                    <div class="d-flex flex-column gap-2">
                        @include('warehouse.partials.breadcrumb', ['title' => 'Product Options'])
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="mdi mdi-package-variant text-primary"></i>
                            Product Options Management
                        </h4>
                    </div>

                    {{-- RIGHT: SEARCH & ACTIONS --}}
                    <div class="flex-shrink-0">
                        @includeIf('warehouse.partials.list-header-product-options')
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
                                    <i class="mdi mdi-package-variant text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-1 small">Total Options</p>
                                <h4 class="mb-0 fw-bold">{{ $options->total() }}</h4>
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
                                <h4 class="mb-0 fw-bold text-success">{{ $options->where('is_active', 1)->count() }}
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
                                <h4 class="mb-0 fw-bold text-warning">{{ $options->where('is_active', 0)->count() }}
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
                                <h4 class="mb-0 fw-bold text-info">{{ $options->count() }}</h4>
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
                    Options List
                </h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-semibold small">#</th>
                                <th class="py-3 text-muted fw-semibold small">ICON & OPTION NAME</th>
                                <th class="py-3 text-muted fw-semibold small">CATEGORY</th>
                                <th class="py-3 text-muted fw-semibold small">SUBCATEGORY</th>
                                <th class="py-3 text-muted fw-semibold small">SKU</th>
                                <th class="py-3 text-muted fw-semibold small text-center">STATUS</th>
                                <th class="px-4 py-3 text-muted fw-semibold small text-end">ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($options as $option)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3 text-muted">
                                        {{ $loop->iteration + ($options->currentPage() - 1) * $options->perPage() }}
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $option->icon ? asset('storage/' . $option->icon) : 'https://placehold.co/40?text=IMG' }}"
                                                class="rounded bg-light border object-fit-cover shadow-sm"
                                                width="40" height="40" alt="{{ $option->option_name }}">
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $option->option_name }}</div>
                                                <small class="text-muted">Unit: {{ strtoupper($option->unit) }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                            {{ $option->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                            {{ $option->subcategory->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <code class="bg-light px-2 py-1 rounded">{{ $option->sku ?? '-' }}</code>
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                                data-id="{{ $option->id }}"
                                                {{ $option->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('warehouse.product-options.edit', $option) }}"
                                                class="btn btn-outline-primary" data-bs-toggle="tooltip"
                                                title="Edit Option">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            <form method="POST"
                                                action="{{ route('warehouse.product-options.destroy', $option) }}"
                                                class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger" data-bs-toggle="tooltip"
                                                    title="Delete Option">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="py-5">
                                            <i class="mdi mdi-package-variant-closed text-muted"
                                                style="font-size: 4rem;"></i>
                                            <p class="text-muted mt-3 mb-0">No product options found.</p>
                                            <a href="{{ route('warehouse.product-options.create') }}"
                                                class="btn btn-sm btn-primary mt-3">
                                                <i class="mdi mdi-plus"></i> Add First Option
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($options->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            Showing {{ $options->firstItem() }} to {{ $options->lastItem() }} of
                            {{ $options->total() }} entries
                        </div>
                        <div>
                            {{ $options->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    @includeIf('warehouse.product-options._import-modal')
    @include('warehouse.product-options._scripts')

</x-app-layout>
