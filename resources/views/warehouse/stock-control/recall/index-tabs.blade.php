<x-app-layout title="Recall Stock Management">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Recall Stock Management'])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div>
            <h4 class="fw-bold mb-0 text-dark">
                <i class="mdi mdi-undo-variant text-warning me-2"></i> Recall Stock Management
            </h4>
            <small class="text-muted">Manage stock recalls, expiry, and damage reports</small>
        </div>
        <a href="{{ route('warehouse.stock-control.recall.create') }}" class="btn btn-primary">
            <i class="mdi mdi-plus-circle me-1"></i> Initiate Recall
        </a>
    </div>

    <!-- Tabs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-bottom">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#myRequests" data-bs-toggle="tab" role="tab">
                        <i class="mdi mdi-clipboard-list me-2"></i> My Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#storeRequests" data-bs-toggle="tab" role="tab">
                        <i class="mdi mdi-inbox me-2"></i> Store Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#expiryDamage" data-bs-toggle="tab" role="tab">
                        <i class="mdi mdi-alert-circle me-2"></i> Expiry & Damage
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                <!-- TAB 1: MY REQUESTS -->
                <div class="tab-pane fade show active" id="myRequests">
                    @include('warehouse.stock-control.recall.tabs.my-requests')
                </div>

                <!-- TAB 2: STORE REQUESTS -->
                <div class="tab-pane fade" id="storeRequests">
                    @include('warehouse.stock-control.recall.tabs.store-requests')
                </div>

                <!-- TAB 3: EXPIRY & DAMAGE -->
                <div class="tab-pane fade" id="expiryDamage">
                    @include('warehouse.stock-control.recall.tabs.expiry-damage')
                </div>

            </div>
        </div>
    </div>

</div>

</x-app-layout>