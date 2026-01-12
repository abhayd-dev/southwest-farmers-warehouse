<x-app-layout title="Create Product">

    <div class="container-fluid">

        {{-- HEADER SECTION --}}
        <div class="bg-white border-bottom shadow-sm mb-4">
            <div class="py-3">
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.partials.breadcrumb', ['title' => 'Create Product'])
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="mdi mdi-plus-circle text-success"></i>
                        Create New Product
                    </h4>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('warehouse.products.store') }}" class="needs-validation" novalidate>
            @csrf

            <div class="row">
                {{-- MAIN FORM --}}
                <div class="col-lg-8">
                    @include('warehouse.products._form')
                </div>

                {{-- SIDEBAR --}}
                <div class="col-lg-4">
                    {{-- INFO CARD --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold">
                                <i class="mdi mdi-information-outline"></i>
                                Quick Tips
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-lightbulb text-warning"></i>
                                    <small class="text-muted">Select a Product Option to auto-fill fields</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-lightbulb text-warning"></i>
                                    <small class="text-muted">Or enter product details manually</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-lightbulb text-warning"></i>
                                    <small class="text-muted">SKU helps in inventory tracking</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <i class="mdi mdi-lightbulb text-warning"></i>
                                    <small class="text-muted">Price should include all applicable taxes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS CARD --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="mdi mdi-check-circle me-1"></i>
                                    Save Product
                                </button>
                                <a href="{{ route('warehouse.products.index') }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="mdi mdi-close-circle me-1"></i>
                                    Cancel
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