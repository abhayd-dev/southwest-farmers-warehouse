<?php

/*
|--------------------------------------------------------------------------
| Route -> permission map
|--------------------------------------------------------------------------
|
| Until now the sidebar was the only thing that enforced permissions: it hid
| links, but any logged-in user could open any page by typing the URL. This
| map is the server-side equivalent of the sidebar's rules, so URL access can
| never exceed what the menu shows.
|
| Patterns match route NAMES (Str::is), first match wins, so put specific
| patterns before wildcards. A value is a permission name, or an array meaning
| "any of these". Super Admins always pass.
|
| PERMISSION_ENFORCEMENT:
|   off     - middleware does nothing
|   log     - (default) requests that WOULD be denied are logged, not blocked
|   enforce - requests are blocked with 403
|
| Roll out by leaving it on "log", reviewing `php artisan permissions:audit`
| and the "permission would be denied" log lines, then switching to enforce.
*/

return [
    'mode' => env('PERMISSION_ENFORCEMENT', 'log'),

    // Reachable by any authenticated user (shared plumbing, lookups, self-service).
    'open' => [
        'dashboard',
        'logout',
        'profile.*',
        'offline.view',
        'api.store.sync',
        'warehouse.notifications.*',
        'warehouse.imports.progress',
        'warehouse.contact-email.resend',
        // Category lookup used by product forms and the PO screen's cascading filters.
        'warehouse.product-options.fetch-subcategories',
        // Kitchen & Catering has no permission gate in the sidebar today; kept
        // as-is (this module is moving to the Store app).
        'kitchen.*',
    ],

    'map' => [
        // Inventory & Operations
        'warehouse.index' => 'view_inventory',
        'warehouse.edit' => 'view_inventory',
        'warehouse.update' => 'view_inventory',
        'warehouse.update-status' => 'view_inventory',
        'warehouse.stocks.*' => 'view_inventory',
        'warehouse.stock.*' => 'view_inventory',
        'warehouse.print.*' => 'view_inventory',

        // Product Catalog
        'warehouse.products.create' => ['create_products', 'manage_products'],
        'warehouse.products.store' => ['create_products', 'manage_products'],
        'warehouse.products.*' => 'view_products',
        'warehouse.promotions.*' => 'manage_promotions',
        'warehouse.categories.*' => 'manage_categories',
        'warehouse.subcategories.*' => 'manage_categories',
        'warehouse.departments.*' => 'manage_categories',
        'warehouse.markets.*' => 'manage_categories',
        'warehouse.market-prices.*' => 'manage_categories',
        'warehouse.product-options.*' => 'manage_categories',

        // Stores
        'warehouse.stores.groups.store' => 'edit_stores',
        'warehouse.stores.groups.update' => 'edit_stores',
        'warehouse.stores.groups.destroy' => 'edit_stores',
        'warehouse.stores.create' => 'create_stores',
        'warehouse.stores.store' => 'create_stores',
        'warehouse.stores.*' => 'view_stores',

        // Procurement
        'warehouse.vendors.*' => 'view_vendors',
        'warehouse.purchase-orders.*' => 'view_po',
        'warehouse.receiving.*' => 'view_po',
        'warehouse.completed-orders.*' => 'view_po',

        // Fulfillment & Dispatch
        'warehouse.store-orders.*' => 'view_stock_requests',
        // The dashboard's "Pending Orders" widget links here for view_stores and
        // approve_store_requests holders (e.g. Regional/General Manager, Store
        // Handler), so they must keep reaching the request list and detail.
        'warehouse.stock-requests.index' => ['view_stock_requests', 'approve_store_requests', 'view_stores'],
        'warehouse.stock-requests.show' => ['view_stock_requests', 'approve_store_requests', 'view_stores'],
        'warehouse.stock-requests.*' => ['view_stock_requests', 'approve_store_requests'],
        'warehouse.discrepancy.*' => 'view_stock_requests',

        // Inventory Control
        'warehouse.stock-control.overview*' => 'view_stock_overview',
        'warehouse.stock-control.repackaging.*' => 'view_stock_overview',
        'warehouse.transfers.*' => 'view_transfers',
        'warehouse.stock-control.recall*' => 'manage_recalls',
        'warehouse.stock-control.valuation*' => 'view_stock_valuation',
        'warehouse.stock-control.minmax.*' => 'manage_min_max',
        'warehouse.stock-control.audit.*' => 'view_audits',
        'warehouse.stock-control.restock-planning' => 'view_inventory',
        'warehouse.stock-control.rules' => 'view_stock_control',
        'warehouse.free-weight.*' => 'view_inventory',
        'warehouse.pallets.*' => 'view_inventory',

        // Finance & Reports
        'warehouse.finance.*' => 'view_financial_reports',
        'warehouse.reports.*' => 'view_expiry_report',

        // Administration
        'warehouse.staff.index' => ['view_staff', 'manage_staff'],
        'warehouse.staff.*' => 'manage_staff',
        'warehouse.roles.*' => 'manage_roles',
        'warehouse.activity-logs.*' => 'view_audit_logs',
        'warehouse.settings.*' => 'manage_settings',

        // Helpdesk
        'warehouse.support.*' => 'view_all_tickets',
        'warehouse.enquiries.*' => 'manage_enquiries',
    ],
];
