<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>
        <div id="sidebar-menu">

            <!-- LOGO -->
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

                <!-- ================= MAIN ================= -->
                <li class="menu-title">Main</li>
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="tp-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:layout-dashboard"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                <!-- ================= INVENTORY MANAGEMENT ================= -->
                <li class="menu-title mt-2">Inventory Management</li>

                <!-- Warehouse -->
                <li>
                    <a href="#sidebarWarehouse" data-bs-toggle="collapse"
                        class="{{ request()->routeIs('warehouse.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:building-warehouse"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Warehouse</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('warehouse.*') ? 'show' : '' }}" id="sidebarWarehouse">
                        <ul class="nav-second-level">
                            <!-- WAREHOUSE (MAIN) -->
                            <li>
                                <a href="{{ route('warehouse.index') }}"
                                    class="tp-link {{ request()->routeIs('warehouse.*') ? 'active' : '' }}">
                                    <iconify-icon icon="tabler:home" class="me-1"></iconify-icon>
                                    Warehouse (Main)
                                </a>
                            </li>
                            <li><a href="#">Master Stock Repository</a></li>
                            <li><a href="#">Receiving Goods</a></li>
                            <li><a href="#">Stock Audits</a></li>
                            <li><a href="#">Damage / Loss Adjustment</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Stores -->
                <li>
                    <a href="#sidebarStores" data-bs-toggle="collapse"
                        class="{{ request()->routeIs('stores.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:building-store"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Stores</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('stores.*') ? 'show' : '' }}" id="sidebarStores">
                        <ul class="nav-second-level">
                            <li><a href="#">Register Stores</a></li>
                            <li><a href="#">Assign Store Managers</a></li>
                            <li><a href="#">Activate / Deactivate Stores</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Products -->
                <li>
                    <a href="#sidebarProducts" data-bs-toggle="collapse"
                        class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:box"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Products</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('products.*') ? 'show' : '' }}" id="sidebarProducts">
                        <ul class="nav-second-level">
                            <li><a href="#">Master Product Catalog</a></li>
                            <li><a href="#">SKU / Barcode / Category</a></li>
                            <li><a href="#">Tax & Pricing Rules</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Stock Management -->
                <li>
                    <a href="#sidebarStock" data-bs-toggle="collapse"
                        class="{{ request()->routeIs('stock.*') ? 'active' : '' }}">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:stack"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Stock Management</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('stock.*') ? 'show' : '' }}" id="sidebarStock">
                        <ul class="nav-second-level">
                            <li><a href="#">Allocate Stock to Stores</a></li>
                            <li><a href="#">Recall Stock</a></li>
                            <li><a href="#">View Store Stock Levels</a></li>
                        </ul>
                    </div>
                </li>

                <!-- ================= ORDERS ================= -->
                <li class="menu-title mt-2">Orders</li>
                <li>
                    <a href="#">
                        <span class="nav-icon">
                            <iconify-icon icon="tabler:shopping-cart"></iconify-icon>
                        </span>
                        <span class="sidebar-text">Store Orders</span>
                    </a>
                </li>

                <!-- ================= SALES & REVENUE ================= -->
                <li class="menu-title mt-2">Sales & Revenue</li>
                <li><a href="#"><iconify-icon icon="tabler:cash"></iconify-icon> Total Revenue</a></li>

                <!-- ================= REPORTS ================= -->
                <li class="menu-title mt-2">Reports</li>
                <li><a href="#"><iconify-icon icon="tabler:report"></iconify-icon> Inventory Reports</a></li>
                <li><a href="#"><iconify-icon icon="tabler:chart-bar"></iconify-icon> Sales Reports</a></li>
                <li><a href="#"><iconify-icon icon="tabler:building"></iconify-icon> Store Performance</a></li>
                <li><a href="#"><iconify-icon icon="tabler:clipboard-check"></iconify-icon> Audit Reports</a>
                </li>

                <!-- ================= TRANSACTIONS ================= -->
                <li class="menu-title mt-2">Transaction History</li>
                <li><a href="#"><iconify-icon icon="tabler:history"></iconify-icon> Ledger</a></li>

                <!-- ================= SUPPORT ================= -->
                <li class="menu-title mt-2">Support</li>
                <li><a href="#"><iconify-icon icon="tabler:headset"></iconify-icon> Support Tickets</a></li>

                <!-- ================= STAFF MANAGEMENT ================= -->
                <li class="menu-title mt-2">Staff Management</li>
                <li><a href="#"><iconify-icon icon="tabler:users"></iconify-icon> Warehouse Staff</a></li>

                <!-- ================= ROLES & PERMISSIONS ================= -->
                <li class="menu-title mt-2">Access Control</li>
                <li><a href="#"><iconify-icon icon="tabler:shield-lock"></iconify-icon> Roles & Permissions</a>
                </li>

                <!-- ================= SETTINGS ================= -->
                <li class="menu-title mt-2">System</li>
                <li><a href="#"><iconify-icon icon="tabler:settings"></iconify-icon> Settings</a></li>

            </ul>
        </div>
    </div>
</div>
