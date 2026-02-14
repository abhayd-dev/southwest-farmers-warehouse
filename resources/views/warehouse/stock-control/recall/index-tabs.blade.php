<x-app-layout title="Recall Stock Management">

<div class="container-fluid">

    {{-- HEADER SECTION --}}
    <div class="bg-white border-bottom shadow-sm mb-4">
        <div class="py-3">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                {{-- Title & Breadcrumb --}}
                <div class="d-flex flex-column gap-2">
                    @include('warehouse.partials.breadcrumb', ['title' => 'Recall Stock Management'])
                    
                </div>
                
                {{-- ACTIONS --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_recalls'))
                <div class="d-flex w-100 w-md-auto gap-2 justify-content-end">
                    <a href="{{ route('warehouse.stock-control.recall.create') }}" class="btn btn-primary shadow-sm flex-fill flex-md-grow-0 text-nowrap">
                        <i class="mdi mdi-plus-circle me-1"></i> Initiate Recall
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TABS & CONTENT --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom pt-3 pb-0 px-3">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="#myRequests" data-bs-toggle="tab" role="tab">
                        <i class="mdi mdi-clipboard-list-outline me-1"></i> My Requests
                        <span class="badge bg-secondary bg-opacity-25 text-secondary ms-1 rounded-pill">Outbound</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="#storeRequests" data-bs-toggle="tab" role="tab">
                        <i class="mdi mdi-inbox-arrow-down me-1"></i> Store Requests
                        <span class="badge bg-warning bg-opacity-25 text-warning ms-1 rounded-pill">Inbound</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="#expiryDamage" data-bs-toggle="tab" role="tab">
                        <i class="mdi mdi-alert-circle-outline me-1"></i> Expiry & Damage
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content p-3">
                <div class="tab-pane fade show active" id="myRequests" role="tabpanel">
                    @include('warehouse.stock-control.recall.tabs.my-requests')
                </div>
                <div class="tab-pane fade" id="storeRequests" role="tabpanel">
                    @include('warehouse.stock-control.recall.tabs.store-requests')
                </div>
                <div class="tab-pane fade" id="expiryDamage" role="tabpanel">
                    @include('warehouse.stock-control.recall.tabs.expiry-damage')
                </div>
            </div>
        </div>
    </div>
</div>

</x-app-layout>