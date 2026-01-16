<x-app-layout title="Store Details - {{ $store->store_name }}">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('warehouse.stores.index') }}">Stores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $store->store_code }}</li>
                    </ol>
                </nav>
                <h4 class="mb-0 fw-bold">{{ $store->store_name }} <span class="badge bg-success fs-6 align-middle ms-2">Active</span></h4>
                <p class="text-muted mb-0"><i class="mdi mdi-map-marker me-1"></i> {{ $store->address }}, {{ $store->city }}</p>
            </div>
            <div>
                <a href="{{ route('warehouse.stores.edit', $store->id) }}" class="btn btn-primary shadow-sm">
                    <i class="mdi mdi-pencil me-1"></i> Edit Details
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-2">Time Period</label>
                        <select class="form-select form-select-sm" id="timePeriod">
                            <option value="today">Today</option>
                            <option value="week" selected>This Week</option>
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-2">Category</label>
                        <select class="form-select form-select-sm">
                            <option selected>All Categories</option>
                            <option value="electronics">Electronics</option>
                            <option value="clothing">Clothing</option>
                            <option value="food">Food & Beverage</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-2">Stock Status</label>
                        <select class="form-select form-select-sm">
                            <option selected>All Items</option>
                            <option value="low">Low Stock</option>
                            <option value="medium">Medium Stock</option>
                            <option value="high">High Stock</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-2">Vendor</label>
                        <select class="form-select form-select-sm">
                            <option selected>All Vendors</option>
                            <option value="vendor1">Vendor A</option>
                            <option value="vendor2">Vendor B</option>
                            <option value="vendor3">Vendor C</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-4 d-flex gap-2">
                        <button class="btn btn-sm btn-primary" title="Apply Filters"><i class="mdi mdi-filter me-1"></i> Apply</button>
                        <button class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="mdi mdi-refresh me-1"></i> Reset</button>
                        <button class="btn btn-sm btn-outline-info" title="Export Report"><i class="mdi mdi-download me-1"></i> Export</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Inventory Value</h6>
                        <h3 class="mb-0 fw-bold">${{ number_format($analytics['inventory_value']) }}</h3>
                        <small class="text-success"><i class="mdi mdi-trending-up me-1"></i>+12.5% from last week</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Total Products</h6>
                        <h3 class="mb-0 fw-bold">{{ $analytics['inventory_items'] }}</h3>
                        <small class="text-muted">Unique SKUs in stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-2 fw-bold">Low Stock Alerts</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $analytics['low_stock_count'] }}</h3>
                        <small class="text-danger fw-bold"><i class="mdi mdi-alert-circle me-1"></i>Action Needed</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-light">
                    <div class="card-body d-flex align-items-center">
                        <div class="avatar-md bg-primary text-white rounded-circle shadow-sm d-flex justify-content-center align-items-center me-3 fw-bold fs-5">
                            {{ substr($store->manager->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">{{ $store->manager->name ?? 'No Manager' }}</h6>
                            <small class="d-block text-muted">{{ $store->manager->email ?? '' }}</small>
                            <small class="d-block text-muted">{{ $store->manager->phone ?? '' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sales & Stock Analytics Chart -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Sales & Stock Analytics</h5>
                            <small class="text-muted">Weekly Performance</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Static Chart Placeholder with SVG -->
                        <svg class="w-100" height="300" viewBox="0 0 800 300" xmlns="http://www.w3.org/2000/svg">
                            <!-- Grid Lines -->
                            <line x1="50" y1="20" x2="50" y2="260" stroke="#e9ecef" stroke-width="2"/>
                            <line x1="50" y1="260" x2="750" y2="260" stroke="#e9ecef" stroke-width="2"/>
                            
                            <!-- Horizontal Grid -->
                            <line x1="50" y1="210" x2="750" y2="210" stroke="#f0f0f0" stroke-width="1"/>
                            <line x1="50" y1="160" x2="750" y2="160" stroke="#f0f0f0" stroke-width="1"/>
                            <line x1="50" y1="110" x2="750" y2="110" stroke="#f0f0f0" stroke-width="1"/>
                            <line x1="50" y1="60" x2="750" y2="60" stroke="#f0f0f0" stroke-width="1"/>
                            
                            <!-- Y-axis labels -->
                            <text x="35" y="265" font-size="12" fill="#6c757d" text-anchor="end">$0</text>
                            <text x="35" y="215" font-size="12" fill="#6c757d" text-anchor="end">$25K</text>
                            <text x="35" y="165" font-size="12" fill="#6c757d" text-anchor="end">$50K</text>
                            <text x="35" y="115" font-size="12" fill="#6c757d" text-anchor="end">$75K</text>
                            <text x="35" y="65" font-size="12" fill="#6c757d" text-anchor="end">$100K</text>
                            
                            <!-- Bar Chart Data (Static) -->
                            <!-- Monday -->
                            <rect x="70" y="160" width="25" height="100" fill="#0d6efd" opacity="0.8" rx="3"/>
                            <text x="82" y="275" font-size="12" fill="#6c757d" text-anchor="middle">Mon</text>
                            
                            <!-- Tuesday -->
                            <rect x="105" y="140" width="25" height="120" fill="#0d6efd" opacity="0.8" rx="3"/>
                            <text x="117" y="275" font-size="12" fill="#6c757d" text-anchor="middle">Tue</text>
                            
                            <!-- Wednesday -->
                            <rect x="140" y="110" width="25" height="150" fill="#0d6efd" opacity="0.8" rx="3"/>
                            <text x="152" y="275" font-size="12" fill="#6c757d" text-anchor="middle">Wed</text>
                            
                            <!-- Thursday -->
                            <rect x="175" y="90" width="25" height="170" fill="#0dcaf0" opacity="0.8" rx="3"/>
                            <text x="187" y="275" font-size="12" fill="#6c757d" text-anchor="middle">Thu</text>
                            
                            <!-- Friday -->
                            <rect x="210" y="50" width="25" height="210" fill="#0dcaf0" opacity="0.8" rx="3"/>
                            <text x="222" y="275" font-size="12" fill="#6c757d" text-anchor="middle">Fri</text>
                            
                            <!-- Saturday -->
                            <rect x="245" y="80" width="25" height="180" fill="#198754" opacity="0.8" rx="3"/>
                            <text x="257" y="275" font-size="12" fill="#6c757d" text-anchor="middle">Sat</text>
                            
                            <!-- Sunday -->
                            <rect x="280" y="120" width="25" height="140" fill="#198754" opacity="0.8" rx="3"/>
                            <text x="292" y="275" font-size="12" fill="#6c757d" text-anchor="middle">Sun</text>
                            
                            <!-- Line Chart Overlay (Sales Trend) -->
                            <polyline points="82,160 117,140 152,110 187,90 222,50 257,80 292,120" stroke="#ffc107" stroke-width="2" fill="none" stroke-linejoin="round" stroke-linecap="round"/>
                            <circle cx="82" cy="160" r="4" fill="#ffc107"/>
                            <circle cx="117" cy="140" r="4" fill="#ffc107"/>
                            <circle cx="152" cy="110" r="4" fill="#ffc107"/>
                            <circle cx="187" cy="90" r="4" fill="#ffc107"/>
                            <circle cx="222" cy="50" r="4" fill="#ffc107"/>
                            <circle cx="257" cy="80" r="4" fill="#ffc107"/>
                            <circle cx="292" cy="120" r="4" fill="#ffc107"/>
                            
                            <!-- Legend -->
                            <g transform="translate(400, 20)">
                                <rect x="0" y="0" width="15" height="15" fill="#0d6efd" rx="2"/>
                                <text x="20" y="12" font-size="12" fill="#495057">Stock Value</text>
                                
                                <rect x="130" y="0" width="15" height="15" fill="#ffc107" rx="2"/>
                                <text x="150" y="12" font-size="12" fill="#495057">Sales Trend</text>
                            </g>
                        </svg>
                        <div class="row g-3 mt-3 pt-3 border-top">
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Avg Daily Sales</small>
                                <h6 class="mb-0 fw-bold">$45,250</h6>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Peak Day</small>
                                <h6 class="mb-0 fw-bold">Friday</h6>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">Total Weekly</small>
                                <h6 class="mb-0 fw-bold">$316,750</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Movement Chart -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Stock Movement by Category</h5>
                            <small class="text-muted">Monthly Distribution</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Donut Chart Style Static Chart -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <svg class="w-100" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Outer Circle -->
                                    <circle cx="100" cy="100" r="90" fill="none" stroke="#e9ecef" stroke-width="30"/>
                                    <!-- Electronics: 35% -->
                                    <circle cx="100" cy="100" r="90" fill="none" stroke="#0d6efd" stroke-width="30" 
                                        stroke-dasharray="197.9 565.5" stroke-dashoffset="0" transform="rotate(-90 100 100)"/>
                                    <!-- Clothing: 25% -->
                                    <circle cx="100" cy="100" r="90" fill="none" stroke="#0dcaf0" stroke-width="30" 
                                        stroke-dasharray="141.3 565.5" stroke-dashoffset="-197.9" transform="rotate(-90 100 100)"/>
                                    <!-- Food: 20% -->
                                    <circle cx="100" cy="100" r="90" fill="none" stroke="#198754" stroke-width="30" 
                                        stroke-dasharray="113" 565.5" stroke-dashoffset="-339.2" transform="rotate(-90 100 100)"/>
                                    <!-- Other: 20% -->
                                    <circle cx="100" cy="100" r="90" fill="none" stroke="#ffc107" stroke-width="30" 
                                        stroke-dasharray="113 565.5" stroke-dashoffset="-452.2" transform="rotate(-90 100 100)"/>
                                    
                                    <!-- Center Circle -->
                                    <circle cx="100" cy="100" r="60" fill="white"/>
                                    <text x="100" y="105" font-size="24" font-weight="bold" text-anchor="middle" fill="#212529">68%</text>
                                    <text x="100" y="125" font-size="12" text-anchor="middle" fill="#6c757d">Utilized</text>
                                </svg>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-circle me-2" style="background-color: #0d6efd; width: 12px; height: 12px;"></span>
                                        <span class="text-muted flex-grow-1">Electronics</span>
                                        <strong>35% ($2,45,000)</strong>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-circle me-2" style="background-color: #0dcaf0; width: 12px; height: 12px;"></span>
                                        <span class="text-muted flex-grow-1">Clothing</span>
                                        <strong>25% ($1,75,000)</strong>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-circle me-2" style="background-color: #198754; width: 12px; height: 12px;"></span>
                                        <span class="text-muted flex-grow-1">Food & Beverage</span>
                                        <strong>20% ($1,40,000)</strong>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-circle me-2" style="background-color: #ffc107; width: 12px; height: 12px;"></span>
                                        <span class="text-muted flex-grow-1">Others</span>
                                        <strong>20% ($1,40,000)</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <!-- Location Details Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="mdi mdi-map-marker-outline me-2 text-primary"></i>Location Details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Code</span>
                                <span class="fw-bold">{{ $store->store_code }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">City</span>
                                <span class="fw-bold">{{ $store->city }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Pincode</span>
                                <span class="fw-bold">{{ $store->pincode }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Created</span>
                                <span class="fw-bold">{{ $store->created_at->format('d M, Y') }}</span>
                            </li>
                        </ul>
                        @if($store->latitude)
                        <div class="mt-3 bg-light rounded p-3 text-center">
                            <small class="text-muted d-block mb-1">Coordinates</small>
                            <code>{{ $store->latitude }}, {{ $store->longitude }}</code>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="mdi mdi-chart-box-outline me-2 text-info"></i>Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Avg Rating</small>
                                    <h6 class="mb-0 fw-bold">4.8<span class="text-warning fs-6">★</span></h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Performance</small>
                                    <h6 class="mb-0 fw-bold text-success">95%</h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Turnover</small>
                                    <h6 class="mb-0 fw-bold">12.5x</h6>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Growth</small>
                                    <h6 class="mb-0 fw-bold text-success">+18%</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Alerts Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="mdi mdi-bell-outline me-2 text-warning"></i>Recent Alerts</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <small class="d-block text-danger fw-bold">Low Stock Alert</small>
                                    <small class="text-muted d-block">Product SKU-2034 below threshold</small>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                                <span class="badge bg-danger rounded-circle" style="width: 8px; height: 8px;"></span>
                            </div>
                            <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <small class="d-block text-warning fw-bold">Stock Transfer</small>
                                    <small class="text-muted d-block">Transfer from Store-A completed</small>
                                    <small class="text-muted">5 hours ago</small>
                                </div>
                                <span class="badge bg-warning rounded-circle" style="width: 8px; height: 8px;"></span>
                            </div>
                            <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <small class="d-block text-success fw-bold">Inventory Updated</small>
                                    <small class="text-muted d-block">Quarterly audit completed</small>
                                    <small class="text-muted">1 day ago</small>
                                </div>
                                <span class="badge bg-success rounded-circle" style="width: 8px; height: 8px;"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>