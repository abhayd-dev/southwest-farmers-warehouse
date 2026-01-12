<x-app-layout title="Edit Product">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.partials.breadcrumb', ['title' => 'Edit Product'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-pencil-circle text-warning"></i>
                        Edit Product
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.products.update', $product) }}" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            <div class="row">
                {{-- MAIN FORM --}}
                <div class="col-lg-8">
                    @include('warehouse.products._form')
                </div>

                {{-- SIDEBAR --}}
                <div class="col-lg-4">
                    {{-- PRODUCT INFO --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-info text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="mdi mdi-cube"></i>
                                Product Details
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Status:</small>
                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'warning' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Created:</small>
                                    <small class="fw-semibold">{{ $product->created_at->format('M d, Y') }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Last Updated:</small>
                                    <small class="fw-semibold">{{ $product->updated_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TIPS CARD --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="mdi mdi-information-outline"></i>
                                Update Guidelines
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-check-circle text-success"></i>
                                    <small class="text-muted">Update price if costs have changed</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-check-circle text-success"></i>
                                    <small class="text-muted">Verify SKU accuracy for inventory</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-check-circle text-success"></i>
                                    <small class="text-muted">Changes will affect all future orders</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS CARD --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg text-white">
                                    <i class="mdi mdi-content-save me-1"></i>
                                    Update Product
                                </button>
                                <a href="{{ route('warehouse.products.index') }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="mdi mdi-arrow-left me-1"></i>
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    @include('warehouse.products._scripts')

</x-app-layout>