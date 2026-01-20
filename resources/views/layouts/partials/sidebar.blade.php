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

                {{-- DASHBOARD --}}
                <li class="menu-title">Overview</li>
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="tp-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:layout-dashboard"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                {{-- INVENTORY & OPERATIONS --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory'))
                    <li class="menu-title mt-2">Inventory & Operations</li>

                    <li>
                        <a href="#sidebarWarehouse" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stocks.*') || request()->routeIs('warehouse.stock-requests.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <iconify-icon icon="tabler:building-warehouse"></iconify-icon>
                            </span>
                            <span class="sidebar-text">Warehouse</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stocks.*') || request()->routeIs('warehouse.stock-requests.*') ? 'show' : '' }}"
                            id="sidebarWarehouse">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stocks.index') }}"
                                        class="{{ request()->routeIs('warehouse.stocks.*') ? 'active' : '' }}">
                                        Stock & Inventory
                                    </a>
                                </li>
                                {{-- DYNAMIC BADGE --}}
                                <li>
                                    <a href="{{ route('warehouse.stock-requests.index') }}"
                                        class="{{ request()->routeIs('warehouse.stock-requests.*') ? 'active' : '' }}">
                                        Stock Requests
                                        @if (isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                                            <span
                                                class="badge bg-danger ms-1 float-end">{{ $pendingRequestsCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- STORES --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores'))
                    <li>
                        <a href="#sidebarStores" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stores.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <iconify-icon icon="tabler:building-store"></iconify-icon>
                            </span>
                            <span class="sidebar-text">Stores</span>
                            {{-- DYNAMIC ALERT ICON --}}
                            @if (isset($lowStockCount) && $lowStockCount > 0)
                                <span class="badge bg-warning text-dark ms-auto me-3"
                                    title="{{ $lowStockCount }} items low on stock">
                                    <i class="mdi mdi-alert"></i>
                                </span>
                            @else
                                <span class="menu-arrow"></span>
                            @endif
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stores.*') ? 'show' : '' }}"
                            id="sidebarStores">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stores.index') }}"
                                        class="{{ request()->routeIs('warehouse.stores.index') ? 'active' : '' }}">
                                        All Stores List
                                    </a>
                                </li>
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_stores'))
                                    <li>
                                        <a href="{{ route('warehouse.stores.create') }}"
                                            class="{{ request()->routeIs('warehouse.stores.create') ? 'active' : '' }}">
                                            Register New Store
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stock_control'))
                    <li class="menu-title mt-2">Inventory & Operations</li>

                    <li>
                        <a href="#sidebarStockControl" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stock-control.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <iconify-icon icon="tabler:settings-cog"></iconify-icon>
                            </span>
                            <span class="sidebar-text">Stock Control</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stock-control.*') ? 'show' : '' }}"
                            id="sidebarStockControl">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stock-control.overview') }}"
                                        class="{{ request()->routeIs('warehouse.stock-control.overview') ? 'active' : '' }}">
                                        Consolidated Stock Overview
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.recall.index') }}"
                                        class="{{ request()->routeIs('warehouse.stock-control.recall.*') ? 'active' : '' }}">
                                        Recall Stock Requests
                                        @if (isset($pendingRecallCount) && $pendingRecallCount > 0)
                                            <span
                                                class="badge bg-warning text-dark ms-1 float-end">{{ $pendingRecallCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.expiry') }}"
                                        class="{{ request()->routeIs('warehouse.stock-control.expiry') ? 'active' : '' }}">
                                        Expiry & Damage Report
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.valuation') }}"
                                        class="{{ request()->routeIs('warehouse.stock-control.valuation') ? 'active' : '' }}">
                                        Stock Valuation
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.minmax.index') }}"
                                        class="{{ request()->routeIs('warehouse.stock-control.minmax.*') ? 'active' : '' }}">
                                        Min-Max Levels
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- PRODUCT CATALOG --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_products'))
                    <li class="menu-title mt-2">Product Catalog</li>

                    <li>
                        <a href="#sidebarProducts" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.products.*') || request()->routeIs('warehouse.product-options.*') || request()->routeIs('warehouse.categories.*') || request()->routeIs('warehouse.subcategories.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <iconify-icon icon="tabler:box-seam"></iconify-icon>
                            </span>
                            <span class="sidebar-text">Products</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.products.*') || request()->routeIs('warehouse.product-options.*') || request()->routeIs('warehouse.categories.*') || request()->routeIs('warehouse.subcategories.*') ? 'show' : '' }}"
                            id="sidebarProducts">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.products.index') }}"
                                        class="{{ request()->routeIs('warehouse.products.index') ? 'active' : '' }}">
                                        All Products
                                    </a>
                                </li>

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_products'))
                                    <li>
                                        <a href="{{ route('warehouse.products.create') }}"
                                            class="{{ request()->routeIs('warehouse.products.create') ? 'active' : '' }}">
                                            Add New Product
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('warehouse.product-options.index') }}"
                                            class="{{ request()->routeIs('warehouse.product-options.*') ? 'active' : '' }}">
                                            Product Options
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('warehouse.categories.index') }}"
                                            class="{{ request()->routeIs('warehouse.categories.*') ? 'active' : '' }}">
                                            Categories
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('warehouse.subcategories.index') }}"
                                            class="{{ request()->routeIs('warehouse.subcategories.*') ? 'active' : '' }}">
                                            Subcategories
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ORDER MANAGEMENT --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_po'))
                    <li class="menu-title mt-2">Order Management</li>

                    <li>
                        <a href="#sidebarOrders" data-bs-toggle="collapse">
                            <span class="nav-icon">
                                <iconify-icon icon="tabler:shopping-cart"></iconify-icon>
                            </span>
                            <span class="sidebar-text">Orders</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarOrders">
                            <ul class="nav-second-level">
                                <li><a href="#">Pending POs</a></li>
                                <li><a href="#">Escalations</a></li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- FINANCE & REPORTS --}}
                @if (auth()->user()->isSuperAdmin() ||
                        auth()->user()->hasPermission('view_financial_reports') ||
                        auth()->user()->hasPermission('view_analytics'))
                    <li class="menu-title mt-2">Finance & Reports</li>

                    @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports'))
                        <li>
                            <a href="#sidebarFinance" data-bs-toggle="collapse">
                                <span class="nav-icon">
                                    <iconify-icon icon="tabler:coin"></iconify-icon>
                                </span>
                                <span class="sidebar-text">Finance</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarFinance">
                                <ul class="nav-second-level">
                                    <li><a href="#">Total Revenue</a></li>
                                    <li><a href="#">Transaction Ledger</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_analytics'))
                        <li>
                            <a href="#sidebarReports" data-bs-toggle="collapse">
                                <span class="nav-icon">
                                    <iconify-icon icon="tabler:chart-infographic"></iconify-icon>
                                </span>
                                <span class="sidebar-text">Reports</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarReports">
                                <ul class="nav-second-level">
                                    <li><a href="#">Inventory Reports</a></li>
                                    <li><a href="#">Sales Analytics</a></li>
                                    <li><a href="#">Store Performance</a></li>
                                    <li><a href="#">Audit Logs</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif
                @endif

                {{-- ADMINISTRATION --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_users'))
                    <li class="menu-title mt-2">Administration</li>

                    <li>
                        <a href="#sidebarStaff" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.staff.*') || request()->routeIs('warehouse.roles.*') ? 'active' : '' }}">
                            <span class="nav-icon">
                                <iconify-icon icon="tabler:users-group"></iconify-icon>
                            </span>
                            <span class="sidebar-text">User Management</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.staff.*') || request()->routeIs('warehouse.roles.*') ? 'show' : '' }}"
                            id="sidebarStaff">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.staff.index') }}"
                                        class="{{ request()->routeIs('warehouse.staff.*') ? 'active' : '' }}">
                                        Warehouse Staff
                                    </a>
                                </li>

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_roles'))
                                    <li>
                                        <a href="{{ route('warehouse.roles.index') }}"
                                            class="{{ request()->routeIs('warehouse.roles.*') ? 'active' : '' }}">
                                            Roles & Permissions
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                <li>
                    <a href="#">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:headset"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Support Tickets</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:settings"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Settings</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</div>
