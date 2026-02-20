@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">📊 Reports & Analytics</h1>
            <p class="text-muted">Gain insights into warehouse operations and sales performance.</p>
        </div>
    </div>

    <div class="row">
        <!-- Sales Report -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Sales Performance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Sales Report</div>
                            <p class="mb-0 small mt-2">View daily/monthly sales by store.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="{{ route('warehouse.reports.sales') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Stock Movement -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Inventory Flow</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Stock Movement</div>
                             <p class="mb-0 small mt-2">Track In/Out stock transactions.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="{{ route('warehouse.reports.stock-movement') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Inventory Health -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Critical Alerts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Inventory Health</div>
                             <p class="mb-0 small mt-2">Low stock & expiring items.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="{{ route('warehouse.reports.inventory-health') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Fast Moving Items -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Top Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Fast Moving Items</div>
                             <p class="mb-0 small mt-2">Identify best-selling products.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rocket fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="{{ route('warehouse.reports.fast-moving') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Top Dispatched -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Store Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Top Dispatched</div>
                             <p class="mb-0 small mt-2">Weight vs Unit dispatches.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck-loading fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="{{ route('warehouse.reports.top-dispatched') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Warehouse Min Health -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Safety Stocks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Warehouse Min</div>
                             <p class="mb-0 small mt-2">Items below minimum levels.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="{{ route('warehouse.reports.warehouse-min') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <!-- Sales By Price Point -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pricing Analytics</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Sales By Price Point</div>
                             <p class="mb-0 small mt-2">Analyze volumes at varying prices.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <a href="{{ route('warehouse.reports.sales-by-price-point') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
