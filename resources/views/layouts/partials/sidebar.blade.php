<div class="app-sidebar-menu">
    <div class="h-100" data-simplebar>
        <div id="sidebar-menu">

            {{-- LOGO --}}
            <div class="logo-box">
                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ Storage::url($settings['main_logo'] ?? '') }}" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ Storage::url($settings['main_logo'] ?? '') }}" height="24">
                    </span>
                </a>
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ Storage::url($settings['main_logo'] ?? '') }}" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ Storage::url($settings['main_logo'] ?? '') }}" height="24">
                    </span>
                </a>
            </div>

            <ul id="sidebar-menu">

                {{-- ================= OVERVIEW ================= --}}
                <li class="menu-title">Overview</li>

                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_dashboard'))
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="tp-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:layout-dashboard"></iconify-icon>
                            <span class="sidebar-text">Dashboard</span>
                        </a>
                    </li>
                @endif

                {{-- ================= INVENTORY & OPERATIONS ================= --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_inventory'))
                    <li class="menu-title mt-2">Inventory & Operations</li>
                    <li>
                        <a href="#sidebarWarehouse" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stocks.*') || request()->routeIs('warehouse.index') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:building-warehouse"></iconify-icon>
                            <span class="sidebar-text">Warehouse</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stocks.*') || request()->routeIs('warehouse.index') ? 'show' : '' }}"
                            id="sidebarWarehouse">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.index') }}"
                                        class="{{ request()->routeIs('warehouse.index') ? 'active' : '' }}">
                                        <i class="mdi mdi-home me-2"></i> My Warehouse
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stocks.index') }}"
                                        class="{{ request()->routeIs('warehouse.stocks.index') ? 'active' : '' }}">
                                        <i class="mdi mdi-package-variant-closed me-2"></i> Stock & Inventory
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= PRODUCT CATALOG ================= --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_products'))
                    <li class="menu-title mt-2">Product Catalog</li>
                    <li>
                        <a href="#sidebarProducts" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.products.*') || request()->routeIs('warehouse.categories.*') || request()->routeIs('warehouse.product-options.*') || request()->routeIs('warehouse.departments.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:box-seam"></iconify-icon>
                            <span class="sidebar-text">Products</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.products.*') || request()->routeIs('warehouse.categories.*') || request()->routeIs('warehouse.product-options.*') || request()->routeIs('warehouse.departments.*') ? 'show' : '' }}"
                            id="sidebarProducts">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.products.index') }}">
                                        <i class="mdi mdi-format-list-bulleted me-2"></i> All Products
                                    </a>
                                </li>

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_products'))
                                    <li>
                                        <a href="{{ route('warehouse.products.create') }}">
                                            <i class="mdi mdi-plus-circle-outline me-2"></i> Add New Product
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_product_options'))
                                    <li>
                                        <a href="{{ route('warehouse.product-options.index') }}">
                                            <i class="mdi mdi-cogs me-2"></i> Product Options
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_categories'))
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
                                    <li>
                                        <a href="{{ route('warehouse.departments.index') }}">
                                            <i class="mdi mdi-view-grid-plus-outline me-2"></i> Departments
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= STORES ================= --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stores'))
                    <li class="menu-title mt-2">Stores</li>
                    <li>
                        <a href="#sidebarStores" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stores.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:building-store"></iconify-icon>
                            <span class="sidebar-text">Stores</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stores.*') ? 'show' : '' }}"
                            id="sidebarStores">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stores.index') }}">
                                        <i class="mdi mdi-store-outline me-2"></i> All Stores List
                                    </a>
                                </li>
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_stores'))
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
                @if (auth()->user()->isSuperAdmin() ||
                        auth()->user()->hasPermission('view_po') ||
                        auth()->user()->hasPermission('view_vendors'))
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
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_vendors'))
                                    <li>
                                        <a href="{{ route('warehouse.vendors.index') }}">
                                            <i class="mdi mdi-account-group-outline me-2"></i> Vendors
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_po'))
                                    <li>
                                        <a href="{{ route('warehouse.purchase-orders.index') }}">
                                            <i class="mdi mdi-file-document-outline me-2"></i> Purchase Orders
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= FULFILLMENT & DISPATCH ================= --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stock_requests'))
                    <li class="menu-title mt-2">Fulfillment & Dispatch</li>
                    <li>
                        <a href="#sidebarStoreOrders" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stock-requests.*') || request()->routeIs('warehouse.discrepancy.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:truck-loading"></iconify-icon>
                            <span class="sidebar-text">
                                Store Orders
                                @if (isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                                    <span class="badge bg-danger rounded-pill ms-2">{{ $pendingRequestsCount }}</span>
                                @endif
                            </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stock-requests.*') || request()->routeIs('warehouse.discrepancy.*') ? 'show' : '' }}"
                            id="sidebarStoreOrders">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.stock-requests.index', ['status' => 'pending']) }}">
                                        <i class="mdi mdi-clock-outline me-2"></i> Pending
                                        @if (isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                                            <span
                                                class="badge bg-danger rounded-pill ms-2">{{ $pendingRequestsCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warehouse.stock-requests.index') }}">
                                        <i class="mdi mdi-history me-2"></i> All Orders History
                                    </a>
                                </li>
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_discrepancies'))
                                    <li>
                                        <a href="{{ route('warehouse.discrepancy.index') }}">
                                            <i class="mdi mdi-alert-circle-outline me-2"></i> Discrepancy
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= STOCK CONTROL (NEW) ================= --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stock_control'))
                    <li class="menu-title mt-2">Stock Control</li>
                    <li>
                        <a href="#sidebarStockControl" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.stock-control.*') || request()->routeIs('warehouse.transfers.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:chart-line"></iconify-icon>
                            <span class="sidebar-text">Stock Control</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.stock-control.*') || request()->routeIs('warehouse.transfers.*') ? 'show' : '' }}"
                            id="sidebarStockControl">
                            <ul class="nav-second-level">

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stock_overview'))
                                    <li>
                                        <a href="{{ route('warehouse.stock-control.overview') }}">
                                            <i class="mdi mdi-view-dashboard-outline me-2"></i> Stock Overview
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_transfers'))
                                    <li>
                                        <a href="{{ route('warehouse.transfers.monitor') }}"
                                            class="{{ request()->routeIs('warehouse.transfers.*') ? 'active' : '' }}">
                                            <i class="mdi mdi-truck-fast-outline me-2"></i> Transfer Monitor
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_recalls'))
                                    <li>
                                        <a href="{{ route('warehouse.stock-control.recall') }}">
                                            <i class="mdi mdi-undo-variant me-2"></i> Recall Stock
                                            @if (isset($pendingRecallCount) && $pendingRecallCount > 0)
                                                <span
                                                    class="badge bg-danger rounded-pill ms-2">{{ $pendingRecallCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_stock_valuation'))
                                    <li>
                                        <a href="{{ route('warehouse.stock-control.valuation') }}">
                                            <i class="mdi mdi-cash-multiple me-2"></i> Stock Valuations
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_min_max'))
                                    <li>
                                        <a href="{{ route('warehouse.stock-control.minmax.index') }}">
                                            <i class="mdi mdi-sort-variant-lock me-2"></i> Min-Max Levels
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_audits'))
                                    <li>
                                        <a href="{{ route('warehouse.stock-control.audit.index') }}"
                                            class="{{ request()->routeIs('warehouse.stock-control.audit.*') ? 'active' : '' }}">
                                            <i class="mdi mdi-clipboard-check-outline me-2"></i> Cycle Counts / Audits
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= FINANCE & REPORTS ================= --}}
                @if (auth()->user()->isSuperAdmin() ||
                        auth()->user()->hasPermission('view_financial_reports') ||
                        auth()->user()->hasPermission('view_expiry_report'))
                    <li class="menu-title mt-2">Finance & Reports</li>
                    <li>
                        {{-- Finance Group --}}
                        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_financial_reports'))
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
                        @endif

                        {{-- Reports Group --}}
                        @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_expiry_report'))
                            <a href="#sidebarReports" data-bs-toggle="collapse"
                                class="{{ request()->routeIs('warehouse.reports.*') ? 'active' : '' }}">
                                <iconify-icon icon="tabler:report"></iconify-icon>
                                <span class="sidebar-text">Reports</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('warehouse.reports.*') ? 'show' : '' }}"
                                id="sidebarReports">
                                <ul class="nav-second-level">
                                    <li>
                                        <a href="{{ route('warehouse.reports.expiry') }}">
                                            <i class="mdi mdi-timer-alert me-2"></i> Expiry Report
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </li>
                @endif

                {{-- ================= ADMINISTRATION ================= --}}
                @if (auth()->user()->isSuperAdmin() ||
                        auth()->user()->hasPermission('view_staff') ||
                        auth()->user()->hasPermission('manage_roles'))
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
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_staff'))
                                    <li>
                                        <a href="{{ route('warehouse.staff.index') }}">
                                            <i class="mdi mdi-card-account-details-outline me-2"></i> Warehouse Staff
                                        </a>
                                    </li>
                                @endif

                                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_roles'))
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

                {{-- ================= SETTINGS ================= --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_settings'))
                    <li>
                        <a href="#sidebarSettings" data-bs-toggle="collapse"
                            class="{{ request()->routeIs('warehouse.settings.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:settings"></iconify-icon>
                            <span class="sidebar-text">Settings</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('warehouse.settings.*') ? 'show' : '' }}"
                            id="sidebarSettings">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('warehouse.settings.general') }}"
                                        class="{{ request()->routeIs('warehouse.settings.general') ? 'active' : '' }}">
                                        <i class="mdi mdi-cog-outline me-2"></i> General Settings
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- ================= HELPDESK ================= --}}
                @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_all_tickets'))
                    <li class="menu-title mt-2">Helpdesk</li>
                    <li>
                        <a href="{{ route('warehouse.support.index') }}"
                            class="tp-link {{ request()->routeIs('warehouse.support.*') ? 'active' : '' }}">
                            <iconify-icon icon="tabler:lifebuoy"></iconify-icon>
                            <span class="sidebar-text">Support Tickets</span>
                            @php
                                $openTickets = \App\Models\SupportTicket::where('status', 'open')->count();
                            @endphp
                            @if ($openTickets > 0)
                                <span class="badge bg-danger rounded-pill ms-auto">{{ $openTickets }}</span>
                            @endif
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
