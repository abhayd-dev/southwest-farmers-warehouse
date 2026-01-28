<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>
        <div id="sidebar-menu">

            {{-- LOGO --}}
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

                {{-- ================= OVERVIEW ================= --}}
                <li class="menu-title">Overview</li>
                <li>
                    <a href="{{ route('dashboard') }}" class="tp-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <iconify-icon icon="tabler:layout-dashboard"></iconify-icon>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                {{-- ================= INVENTORY & OPERATIONS ================= --}}
                {{-- Visible to: Inventory Manager, VP Ops, Super Admin, Store Handler --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory'))
                    <li class="menu-title mt-2">Inventory & Operations</li>

                    <li>
                        <a href="#sidebarWarehouse" data-bs-toggle="collapse"
                           class="{{ request()->routeIs('warehouse.stocks.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:building-warehouse"></iconify-icon>
                            <span class="sidebar-text">Warehouse</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stocks.*') ? 'show' : '' }}" id="sidebarWarehouse">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stocks.index') }}"
                                       class="{{ request()->routeIs('warehouse.stocks.index') ? 'active' : '' }}">
                                        <i class="mdi mdi-package-variant-closed me-2"></i> Stock & Inventory
                                    </a>
                                </li>
                                {{-- Only those who can report damages --}}
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('report_damages'))
                                {{-- Note: Usually linked from stock index, but if you have a direct page: --}}
                                {{-- <li><a href="#"><i class="mdi mdi-alert-circle-outline me-2"></i> Damaged Stock</a></li> --}}
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= PRODUCT CATALOG ================= --}}
                {{-- Visible to: Brand Manager, Super Admin, Inventory Manager --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_products'))
                    <li class="menu-title mt-2">Product Catalog</li>

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
                                <li>
                                    <a href="{{ route('warehouse.products.index') }}">
                                        <i class="mdi mdi-format-list-bulleted me-2"></i> All Products
                                    </a>
                                </li>
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_products'))
                                    <li>
                                        <a href="{{ route('warehouse.products.create') }}">
                                            <i class="mdi mdi-plus-circle-outline me-2"></i> Add New Product
                                        </a>
                                    </li>
                                @endif
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_product_options'))
                                    <li>
                                        <a href="{{ route('warehouse.product-options.index') }}">
                                            <i class="mdi mdi-cogs me-2"></i> Product Options
                                        </a>
                                    </li>
                                @endif
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
                                    <li>
                                        <a href="{{ route('warehouse.categories.index') }}">
                                            <i class="mdi mdi-shape-outline me-2"></i> Categories
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('warehouse.subcategories.index') }}">
                                            <i class="mdi mdi-shape-plus-outline me-2"></i> Subcategories
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= STORES ================= --}}
                {{-- Visible to: Regional Manager, General Manager, Store Handler --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores'))
                    <li class="menu-title mt-2">Stores</li>

                    <li>
                        <a href="#sidebarStores" data-bs-toggle="collapse"
                           class="{{ request()->routeIs('warehouse.stores.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:building-store"></iconify-icon>
                            <span class="sidebar-text">Stores</span>
                            <span class="menu-arrow"></span>
                        </a>

                        <div class="collapse {{ request()->routeIs('warehouse.stores.*') ? 'show' : '' }}" id="sidebarStores">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stores.index') }}">
                                        <i class="mdi mdi-store-outline me-2"></i> All Stores List
                                    </a>
                                </li>
                                @if(auth()->user()->isSuperAdmin())
                                    <li>
                                        <a href="{{ route('warehouse.stores.create') }}">
                                            <i class="mdi mdi-domain-plus me-2"></i> Register New Store
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= PROCUREMENT ================= --}}
                {{-- Visible to: Purchase Manager, VP Ops, CFO --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_po'))
                    <li class="menu-title mt-2">Procurement</li>

                    <li>
                        <a href="#sidebarProcurement" data-bs-toggle="collapse"
                           class="{{ request()->routeIs('warehouse.vendors.*') || request()->routeIs('warehouse.purchase-orders.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:truck-delivery"></iconify-icon>
                            <span class="sidebar-text">Purchases & Supply</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.vendors.*') || request()->routeIs('warehouse.purchase-orders.*') ? 'show' : '' }}"
                             id="sidebarProcurement">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.vendors.index') }}">
                                        <i class="mdi mdi-account-group-outline me-2"></i> Vendor Management
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.purchase-orders.index') }}">
                                        <i class="mdi mdi-file-document-outline me-2"></i> Purchase Orders (PO)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                {{-- ================= FULFILLMENT & DISPATCH ================= --}}
                {{-- Visible to: Store Handler, Regional Manager, General Manager --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('approve_store_requests') || auth()->user()->hasPermission('manage_store_inventory'))
                    <li class="menu-title mt-2">Fulfillment & Dispatch</li>

                    <li>
                        <a href="#sidebarStoreOrders" data-bs-toggle="collapse"
                           class="{{ request()->routeIs('warehouse.stock-requests.*') || request()->routeIs('warehouse.discrepancy.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:truck-loading"></iconify-icon>
                            <span class="sidebar-text">Store Orders</span>
                            {{-- Optional: Badge for pending requests can be passed from AppServiceProvider --}}
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stock-requests.*') || request()->routeIs('warehouse.discrepancy.*') ? 'show' : '' }}"
                             id="sidebarStoreOrders">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stock-requests.index', ['status' => 'pending']) }}">
                                        <i class="mdi mdi-clock-outline me-2"></i> Pending Requests
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-requests.index') }}">
                                        <i class="mdi mdi-history me-2"></i> All Orders History
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.discrepancy.index') }}">
                                        <i class="mdi mdi-alert-circle-outline me-2"></i> Discrepancy / Returns
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= STOCK CONTROL ================= --}}
                {{-- Visible to: Inventory Manager, VP Ops, Super Admin --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_inventory'))
                    <li class="menu-title mt-2">Stock Control</li>

                    <li>
                        <a href="#sidebarStockControl" data-bs-toggle="collapse"
                           class="{{ request()->routeIs('warehouse.stock-control.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:chart-line"></iconify-icon>
                            <span class="sidebar-text">Stock Control</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stock-control.*') ? 'show' : '' }}"
                             id="sidebarStockControl">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stock-control.overview') }}">
                                        <i class="mdi mdi-view-dashboard-outline me-2"></i> Stock Overview
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.recall') }}">
                                        <i class="mdi mdi-undo-variant me-2"></i> Recall Stock
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.valuation') }}">
                                        <i class="mdi mdi-cash-multiple me-2"></i> Stock Valuations
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-control.minmax.index') }}">
                                        <i class="mdi mdi-sort-variant-lock me-2"></i> Min-Max Levels
                                    </a>
                                </li>
                                {{-- Rules: Future Feature --}}
                                <li>
                                    <a href="{{ route('warehouse.stock-control.rules') }}">
                                        <i class="mdi mdi-gavel me-2"></i> Rules
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= FINANCE & REPORTS ================= --}}
                {{-- Visible to: CFO, Accountant, CEO --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports'))
                    <li class="menu-title mt-2">Finance & Reports</li>

                    <li>
                        <a href="#sidebarFinance" data-bs-toggle="collapse"
                           class="{{ request()->routeIs('warehouse.finance.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:coin"></iconify-icon>
                            <span class="sidebar-text">Finance</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.finance.*') ? 'show' : '' }}"
                             id="sidebarFinance">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.finance.index') }}">
                                        <i class="mdi mdi-chart-line me-2"></i> Total Revenue
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.finance.ledger') }}">
                                        <i class="mdi mdi-book-open-page-variant me-2"></i> Transaction Ledger
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= ADMINISTRATION ================= --}}
                {{-- Visible to: Super Admin only --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_users'))
                    <li class="menu-title mt-2">Administration</li>

                    <li>
                        <a href="#sidebarStaff" data-bs-toggle="collapse"
                           class="{{ request()->routeIs('warehouse.staff.*') || request()->routeIs('warehouse.roles.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:users-group"></iconify-icon>
                            <span class="sidebar-text">User Management</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.staff.*') || request()->routeIs('warehouse.roles.*') ? 'show' : '' }}"
                             id="sidebarStaff">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.staff.index') }}">
                                        <i class="mdi mdi-card-account-details-outline me-2"></i> Warehouse Staff
                                    </a>
                                </li>
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_roles'))
                                    <li>
                                        <a href="{{ route('warehouse.roles.index') }}">
                                            <i class="mdi mdi-shield-account-outline me-2"></i> Roles & Permissions
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= SUPPORT & SETTINGS ================= --}}
                {{-- Visible to everyone --}}
                <li class="menu-title mt-2">Support</li>

                <li>
                    <a href="#">
                        <iconify-icon icon="tabler:headset"></iconify-icon>
                        <span class="sidebar-text">Support Tickets</span>
                    </a>
                </li>
                
                {{-- Settings: Restricted to Admin/Managers if needed --}}
                @if(auth()->user()->isSuperAdmin())
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