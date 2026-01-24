<x-app-layout title="Stock Control Rules">

<div class="container-fluid">

    @include('warehouse.partials.breadcrumb', ['title' => 'Stock Control Rules & Policies'])

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="mdi mdi-rule text-primary me-2"></i> Stock Control Rules & Policies
        </h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">
            <i class="mdi mdi-plus-circle me-1"></i> Add New Rule
        </button>
    </div>

    <div class="row">
        <!-- Auto-Replenishment Rule -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Auto-Replenishment</h5>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">When product stock falls below minimum level, automatically generate purchase orders.</p>
                    <ul class="list-unstyled">
                        <li><i class="mdi mdi-check text-success me-2"></i> Monitors Min-Max levels daily</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> Triggers PO at min threshold</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> Prevents stockouts</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> Notification alerts</li>
                    </ul>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-pencil me-1"></i> Configure
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiry Management Rule -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Expiry Management</h5>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Automatically flag and manage products nearing expiration.</p>
                    <ul class="list-unstyled">
                        <li><i class="mdi mdi-check text-success me-2"></i> 90-day expiry alert</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> 30-day critical warning</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> Recall suggestions</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> FIFO enforcement</li>
                    </ul>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-pencil me-1"></i> Configure
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Damage & Loss Rule -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Damage & Loss Tracking</h5>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Monitor and manage damaged or lost inventory.</p>
                    <ul class="list-unstyled">
                        <li><i class="mdi mdi-check text-success me-2"></i> Automatic write-offs</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> Damage tracking</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> Loss reporting</li>
                        <li><i class="mdi mdi-check text-success me-2"></i> Audit trail</li>
                    </ul>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-pencil me-1"></i> Configure
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Transfer Rule -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Intelligent Stock Transfer</h5>
                        <span class="badge bg-warning">Coming Soon</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Automatically redistribute stock across stores based on demand.</p>
                    <ul class="list-unstyled">
                        <li><i class="mdi mdi-lock text-muted me-2"></i> Demand-based transfer</li>
                        <li><i class="mdi mdi-lock text-muted me-2"></i> Load balancing</li>
                        <li><i class="mdi mdi-lock text-muted me-2"></i> Optimization engine</li>
                        <li><i class="mdi mdi-lock text-muted me-2"></i> Cost reduction</li>
                    </ul>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary" disabled>
                            <i class="mdi mdi-lock me-1"></i> Coming Soon
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rules Activity Log -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Rule Execution History</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="mdi mdi-information-outline me-2"></i>
                <strong>Rules are automatically executed daily at 2:00 AM IST.</strong> View execution logs below.
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Rule</th>
                            <th>Last Execution</th>
                            <th>Status</th>
                            <th>Actions Triggered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Auto-Replenishment</strong></td>
                            <td>Today, 2:00 AM</td>
                            <td><span class="badge bg-success">Success</span></td>
                            <td>3 POs generated</td>
                        </tr>
                        <tr>
                            <td><strong>Expiry Management</strong></td>
                            <td>Today, 2:05 AM</td>
                            <td><span class="badge bg-success">Success</span></td>
                            <td>5 alerts sent</td>
                        </tr>
                        <tr>
                            <td><strong>Damage Tracking</strong></td>
                            <td>Yesterday, 2:10 AM</td>
                            <td><span class="badge bg-success">Success</span></td>
                            <td>2 write-offs</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

</x-app-layout>