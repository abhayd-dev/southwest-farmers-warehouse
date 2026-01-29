<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>
        <div id="sidebar-menu">
            <div class="logo-box">
                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo.jpg') }}" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/logo.jpg') }}" height="24">
                    </span>
                </a>
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/logo.jpg') }}" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/logo.jpg') }}" height="24">
                    </span>
                </a>
            </div>
            <ul id="sidebar-menu">
                <li class="menu-title">Overview</li>
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="tp-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <iconify-icon icon="tabler:layout-dashboard"></iconify-icon>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory'))
                    <li class="menu-title mt-2">Operations</li>
                    <li>
                        <a href="#sidebarWarehouse" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stocks.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:building-warehouse"></iconify-icon>
                            <span class="sidebar-text">Warehouse</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stocks.*') ? 'show' : '' }}"
                            id="sidebarWarehouse">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stocks.index') }}"
                                        class="{{ request()->routeIs('warehouse.stocks.index') ? 'active' : '' }}">
                                        <span class="sidebar-text">Stock & Inventory</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_products'))
                    <li class="menu-title mt-2">Catalog</li>
                    <li>
                        <a href="#sidebarProducts" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.products.*') || request()->routeIs('warehouse.categories.*') || request()->routeIs('warehouse.product-options.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:box-seam"></iconify-icon>
                            <span class="sidebar-text">Products</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.products.*') || request()->routeIs('warehouse.categories.*') || request()->routeIs('warehouse.product-options.*') ? 'show' : '' }}"
                            id="sidebarProducts">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('warehouse.products.index') }}">All Products</a></li>
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_products'))
                                    <li><a href="{{ route('warehouse.products.create') }}">Add Product</a></li>
                                @endif
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_product_options'))
                                    <li><a href="{{ route('warehouse.product-options.index') }}">Options</a></li>
                                @endif
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                    <li><a href="{{ route('warehouse.categories.index') }}">Categories</a></li>
                                    <li><a href="{{ route('warehouse.subcategories.index') }}">Subcategories</a></li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores'))
                    <li class="menu-title mt-2">Retail</li>
                    <li>
                        <a href="#sidebarStores" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stores.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:building-store"></iconify-icon>
                            <span class="sidebar-text">Stores</span>
                            @if (isset($lowStockCount) && $lowStockCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto">{{ $lowStockCount }}</span>
                            @else
                                <span class="menu-arrow"></span>
                            @endif
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stores.*') ? 'show' : '' }}"
                            id="sidebarStores">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('warehouse.stores.index') }}">Store List</a></li>
                                @if (auth()->user()->isSuperAdmin())
                                    <li><a href="{{ route('warehouse.stores.create') }}">Add Store</a></li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_po'))
                    <li class="menu-title mt-2">Procurement</li>
                    <li>
                        <a href="#sidebarProcurement" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.vendors.*') || request()->routeIs('warehouse.purchase-orders.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:truck-delivery"></iconify-icon>
                            <span class="sidebar-text">Supply</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.vendors.*') || request()->routeIs('warehouse.purchase-orders.*') ? 'show' : '' }}"
                            id="sidebarProcurement">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('warehouse.vendors.index') }}">Vendors</a></li>
                                <li><a href="{{ route('warehouse.purchase-orders.index') }}">Purchase Orders</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() ||
                        auth()->user()->hasPermission('approve_store_requests') ||
                        auth()->user()->hasPermission('manage_store_inventory'))
                    <li class="menu-title mt-2">Fulfillment</li>
                    <li>
                        <a href="#sidebarStoreOrders" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stock-requests.*') || request()->routeIs('warehouse.discrepancy.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:truck-loading"></iconify-icon>
                            <span class="sidebar-text">Orders</span>
                            @if (isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingRequestsCount }}</span>
                            @else
                                <span class="menu-arrow"></span>
                            @endif
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stock-requests.*') || request()->routeIs('warehouse.discrepancy.*') ? 'show' : '' }}"
                            id="sidebarStoreOrders">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stock-requests.index', ['status' => 'pending']) }}"
                                        class="d-flex align-items-center justify-content-between">
                                        <span>Pending</span>
                                        @if (isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                                            <span
                                                class="badge bg-danger rounded-pill">{{ $pendingRequestsCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li><a href="{{ route('warehouse.stock-requests.index') }}">History</a></li>
                                <li><a href="{{ route('warehouse.discrepancy.index') }}">Discrepancies</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_inventory'))
                    <li class="menu-title mt-2">Control</li>
                    <li>
                        <a href="#sidebarStockControl" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stock-control.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:chart-line"></iconify-icon>
                            <span class="sidebar-text">Analytics</span>
                            @if (isset($pendingRecallCount) && $pendingRecallCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingRecallCount }}</span>
                            @else
                                <span class="menu-arrow"></span>
                            @endif
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stock-control.*') ? 'show' : '' }}"
                            id="sidebarStockControl">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('warehouse.stock-control.overview') }}">Overview</a></li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.recall') }}"
                                        class="d-flex align-items-center justify-content-between">
                                        <span>Recall Stock</span>
                                        @if (isset($pendingRecallCount) && $pendingRecallCount > 0)
                                            <span
                                                class="badge bg-danger rounded-pill">{{ $pendingRecallCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li><a href="{{ route('warehouse.stock-control.valuation') }}">Valuation</a></li>
                                <li><a href="{{ route('warehouse.stock-control.minmax.index') }}">Min-Max Levels</a>
                                </li>
                                <li><a href="{{ route('warehouse.stock-control.rules') }}">Rules</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports'))
                    <li class="menu-title mt-2">Finance</li>
                    <li>
                        <a href="#sidebarFinance" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.finance.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:coin"></iconify-icon>
                            <span class="sidebar-text">Reports</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.finance.*') ? 'show' : '' }}"
                            id="sidebarFinance">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('warehouse.finance.index') }}">Revenue</a></li>
                                <li><a href="{{ route('warehouse.finance.ledger') }}">Ledger</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_users'))
                    <li class="menu-title mt-2">Admin</li>
                    <li>
                        <a href="#sidebarStaff" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.staff.*') || request()->routeIs('warehouse.roles.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:users-group"></iconify-icon>
                            <span class="sidebar-text">Users</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.staff.*') || request()->routeIs('warehouse.roles.*') ? 'show' : '' }}"
                            id="sidebarStaff">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('warehouse.staff.index') }}">Staff</a></li>
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_roles'))
                                    <li><a href="{{ route('warehouse.roles.index') }}">Roles</a></li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                <li class="menu-title mt-2">Help</li>
                <li>
                    <a href="#">
                        <iconify-icon icon="tabler:headset"></iconify-icon>
                        <span class="sidebar-text">Support</span>
                    </a>
                </li>

                @if (auth()->user()->isSuperAdmin())
                    <li>
                        <a href="#">
                            <iconify-icon icon="tabler:settings"></iconify-icon>
                            <span class="sidebar-text">Settings</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

</div>
