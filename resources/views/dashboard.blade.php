<x-app-layout title="Dashboard | Warehouse POS">

    <div class="container-fluid">

        <!-- PAGE HEADER -->
        <div class="py-3 d-flex align-items-center justify-content-between">
            <h4 class="fs-18 fw-semibold m-0">Warehouse Dashboard</h4>
            <span class="text-muted fs-14">Live warehouse overview</span>
        </div>

        <!-- ================= KPI CARDS ================= -->
        <div class="row">

            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Stock Value</p>
                        <h3 class="fw-semibold text-success">$ 12,45,000</h3>
                        <small class="text-muted">All warehouses</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Products</p>
                        <h3 class="fw-semibold">1,280</h3>
                        <small class="text-muted">Active SKUs</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Pending Orders</p>
                        <h3 class="fw-semibold text-warning">34</h3>
                        <small class="text-muted">Store requests</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Low Stock Items</p>
                        <h3 class="fw-semibold text-danger">18</h3>
                        <small class="text-muted">Needs restock</small>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= CHARTS ================= -->
        <div class="row mt-2">

            <!-- STOCK MOVEMENT BAR CHART -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Monthly Stock Movement</h5>
                    </div>
                    <div class="card-body">
                        <div id="stockMovementChart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>

            <!-- ORDER STATUS PIE CHART -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Status Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div id="orderStatusChart" style="height: 320px;"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= RECENT ORDERS ================= -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card mt-2">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">Recent Store Orders</h5>
                        <a href="#" class="btn btn-sm btn-outline-success">View All</a>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Store</th>
                                    <th>Order ID</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Lucknow Store #12</td>
                                    <td>#ORD-4587</td>
                                    <td>24</td>
                                    <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>Kanpur Store #05</td>
                                    <td>#ORD-4586</td>
                                    <td>18</td>
                                    <td><span class="badge bg-success-subtle text-success">Dispatched</span></td>
                                </tr>
                                <tr>
                                    <td>Varanasi Store #03</td>
                                    <td>#ORD-4585</td>
                                    <td>32</td>
                                    <td><span class="badge bg-primary-subtle text-primary">Delivered</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/warehouse-dashboard.init.js') }}"></script>
    @endpush

</x-app-layout>
