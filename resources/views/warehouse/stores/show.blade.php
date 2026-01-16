<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Details Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Stores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">STR-001</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">Premium Store Mumbai <span class="badge bg-success fs-6 align-middle ms-2">Active</span></h4>
                <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i> 123 Business Park, Mumbai, Maharashtra</p>
            </div>
            <div>
                <button class="btn btn-primary shadow-sm">
                    <i class="fas fa-pen me-2"></i>Edit Details
                </button>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fs-7">Inventory Value</h6>
                        <h3 class="mb-0 fw-bold">₹24,50,000</h3>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i>+5.2% from last month</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fs-7">Total Products</h6>
                        <h3 class="mb-0 fw-bold">1,245</h3>
                        <small class="text-muted">Unique SKUs in stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fs-7">Low Stock Alerts</h6>
                        <h3 class="mb-0 fw-bold text-warning">28</h3>
                        <small class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i>Action Needed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-light">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 fw-bold">Rajesh Kumar</h6>
                            <small class="d-block text-muted">Store Manager</small>
                        </div>
                        <div class="avatar-md bg-primary text-white rounded-circle d-flex justify-content-center align-items-center fs-5 fw-bold">
                            R
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-filter text-primary me-2"></i>
                    <h6 class="mb-0 fw-bold">Filters</h6>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-5 text-muted">Date Range</label>
                        <select class="form-select form-select-sm">
                            <option selected>Last 30 Days</option>
                            <option>Last 7 Days</option>
                            <option>Last 90 Days</option>
                            <option>Last 6 Months</option>
                            <option>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-5 text-muted">Category</label>
                        <select class="form-select form-select-sm">
                            <option selected>All Categories</option>
                            <option>Electronics</option>
                            <option>Clothing</option>
                            <option>Home & Garden</option>
                            <option>Books</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-5 text-muted">Stock Status</label>
                        <select class="form-select form-select-sm">
                            <option selected>All Items</option>
                            <option>In Stock</option>
                            <option>Low Stock</option>
                            <option>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-5 text-muted">Sort By</label>
                        <select class="form-select form-select-sm">
                            <option selected>Recent</option>
                            <option>Alphabetical</option>
                            <option>Price High to Low</option>
                            <option>Price Low to High</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Section -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">Sales & Inventory Trends</h5>
                    </div>
                    <div class="card-body p-4">
                        <canvas id="trendsChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">Stock Distribution</h5>
                    </div>
                    <div class="card-body p-4">
                        <canvas id="distributionChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Details & Category Breakdown -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">Location Details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted"><i class="fas fa-code me-2"></i>Store Code</span>
                                <span class="fw-bold">STR-001</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted"><i class="fas fa-city me-2"></i>City</span>
                                <span class="fw-bold">Mumbai</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted"><i class="fas fa-mailbox me-2"></i>Pincode</span>
                                <span class="fw-bold">400001</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted"><i class="fas fa-calendar me-2"></i>Created</span>
                                <span class="fw-bold">15 Jan, 2024</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                <span class="text-muted"><i class="fas fa-user-tie me-2"></i>Manager</span>
                                <span class="fw-bold">Rajesh Kumar</span>
                            </li>
                        </ul>
                        <div class="mt-4 bg-light rounded p-3">
                            <small class="text-muted d-block mb-2"><i class="fas fa-location-dot me-1"></i>Coordinates</small>
                            <code class="fw-5">19.0760°N, 72.8777°E</code>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">Category Performance</h5>
                    </div>
                    <div class="card-body p-4">
                        <canvas id="categoryChart" height="90"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="row g-4 mt-1">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold">Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center g-3">
                            <div class="col-md-3">
                                <div class="py-3">
                                    <h4 class="text-primary fw-bold mb-1">₹45.2L</h4>
                                    <small class="text-muted">Monthly Revenue</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="py-3 border-start border-end">
                                    <h4 class="text-success fw-bold mb-1">2,340</h4>
                                    <small class="text-muted">Units Sold</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="py-3">
                                    <h4 class="text-warning fw-bold mb-1">94.5%</h4>
                                    <small class="text-muted">Stock Accuracy</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="py-3">
                                    <h4 class="text-info fw-bold mb-1">125</h4>
                                    <small class="text-muted">Active SKUs</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan 1', 'Jan 8', 'Jan 15', 'Jan 22', 'Jan 29', 'Feb 5', 'Feb 12'],
                datasets: [
                    {
                        label: 'Sales (₹)',
                        data: [28000, 32000, 35000, 31000, 42000, 38000, 45000],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#0d6efd',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Inventory Value (₹)',
                        data: [2400000, 2450000, 2320000, 2380000, 2500000, 2480000, 2450000],
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: { size: 12, weight: 'bold' }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // Distribution Chart (Doughnut)
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: [
                        '#198754',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });

        // Category Chart (Horizontal Bar)
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: ['Electronics', 'Clothing', 'Home & Garden', 'Books', 'Sports'],
                datasets: [{
                    label: 'Items in Stock',
                    data: [320, 280, 220, 180, 165],
                    backgroundColor: [
                        '#0d6efd',
                        '#6c757d',
                        '#198754',
                        '#ffc107',
                        '#0dcaf0'
                    ],
                    borderRadius: 5,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>