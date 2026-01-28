<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Warehouse\Auth\LoginController;
use App\Http\Controllers\Warehouse\Auth\ForgotPasswordController;
use App\Http\Controllers\Warehouse\Auth\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Warehouse\DiscrepancyController;
use App\Http\Controllers\Warehouse\FinanceReportController;
use App\Http\Controllers\Warehouse\MinMaxController;
use App\Http\Controllers\Warehouse\ProductController;
use App\Http\Controllers\Warehouse\ProductOptionController;
use App\Http\Controllers\Warehouse\ProductCategoryController;
use App\Http\Controllers\Warehouse\ProductStockController;
use App\Http\Controllers\Warehouse\ProductSubcategoryController;
use App\Http\Controllers\Warehouse\PurchaseOrderController;
use App\Http\Controllers\Warehouse\RecallController;
use App\Http\Controllers\Warehouse\RolePermissionController;
use App\Http\Controllers\Warehouse\StaffController;
use App\Http\Controllers\Warehouse\StockControlController;
use App\Http\Controllers\Warehouse\StockRequestController;
use App\Http\Controllers\Warehouse\StoreController;
use App\Http\Controllers\Warehouse\VendorController;
use App\Http\Controllers\WarehouseController;

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [WarehouseController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
    Route::get('/warehouse/edit/{warehouse}', [WarehouseController::class, 'edit'])->name('warehouse.edit');
    Route::put('/warehouse/update/{warehouse}', [WarehouseController::class, 'update'])->name('warehouse.update');
    Route::post('/warehouse/update-status', [WarehouseController::class, 'updateStatus'])->name('warehouse.update-status');

    Route::prefix('warehouse')->group(function () {

        Route::resource('product-options', ProductOptionController::class)->names('warehouse.product-options')->except(['show']);
        Route::post('product-options/status', [ProductOptionController::class, 'changeStatus'])->name('warehouse.product-options.status');
        Route::post('product-options/import', [ProductOptionController::class, 'import'])->name('warehouse.product-options.import');
        Route::get('product-options/export', [ProductOptionController::class, 'export'])->name('warehouse.product-options.export');
        Route::get('product-options/sample', [ProductOptionController::class, 'sample'])->name('warehouse.product-options.sample');
        Route::get('fetch-subcategories/{category}', [ProductOptionController::class, 'fetchSubcategories'])->name('warehouse.product-options.fetch-subcategories');

        Route::resource('categories', ProductCategoryController::class)->names('warehouse.categories')->except(['show']);
        Route::post('categories/status', [ProductCategoryController::class, 'changeStatus'])->name('warehouse.categories.status');
        Route::post('categories/import', [ProductCategoryController::class, 'import'])->name('warehouse.categories.import');
        Route::get('categories/export', [ProductCategoryController::class, 'export'])->name('warehouse.categories.export');
        Route::get('categories/sample', [ProductCategoryController::class, 'sample'])->name('warehouse.categories.sample');

        Route::resource('subcategories', ProductSubcategoryController::class)->names('warehouse.subcategories')->except(['show']);
        Route::post('subcategories/status', [ProductSubcategoryController::class, 'changeStatus'])->name('warehouse.subcategories.status');
        Route::post('subcategories/import', [ProductSubcategoryController::class, 'import'])->name('warehouse.subcategories.import');
        Route::get('subcategories/export', [ProductSubcategoryController::class, 'export'])->name('warehouse.subcategories.export');
        Route::get('subcategories/sample', [ProductSubcategoryController::class, 'sample'])->name('warehouse.subcategories.sample');

        Route::resource('products', ProductController::class)->names('warehouse.products')->except(['show']);
        Route::post('products/status', [ProductController::class, 'changeStatus'])->name('warehouse.products.status');
        Route::post('products/import', [ProductController::class, 'import'])->name('warehouse.products.import');
        Route::get('products/export', [ProductController::class, 'export'])->name('warehouse.products.export');
        Route::get('products/sample', [ProductController::class, 'sample'])->name('warehouse.products.sample');
        Route::get('products/fetch-option/{option}', [ProductController::class, 'fetchOption'])->name('warehouse.products.fetch-option');

        Route::controller(ProductStockController::class)->group(function () {
            Route::get('stocks', 'index')->name('warehouse.stocks.index');
            Route::get('stocks/create', 'create')->name('warehouse.stocks.create');
            Route::post('stocks/store', 'store')->name('warehouse.stocks.store');
            Route::get('stocks/product-details/{product}', 'getProductDetails')->name('warehouse.stocks.product-details');
            Route::get('stocks/{product}/history', 'history')->name('warehouse.stocks.history');
            Route::get('stocks/adjust', 'adjust')->name('warehouse.stocks.adjust');
            Route::post('stocks/adjust', 'storeAdjustment')->name('warehouse.stocks.store-adjustment');
            Route::post('/stock/mark-damaged',  'markAsDamaged')->name('warehouse.stock.mark-damaged');
        });

        Route::resource('roles', RolePermissionController::class)->names('warehouse.roles');
        Route::post('staff/status', [StaffController::class, 'changeStatus'])->name('warehouse.staff.status');
        Route::resource('staff', StaffController::class)->names('warehouse.staff');

        Route::post('stores/update-status', [StoreController::class, 'updateStatus'])->name('warehouse.stores.update-status');
        Route::get('stores/{id}/analytics', [StoreController::class, 'analytics'])->name('warehouse.stores.analytics');
        Route::resource('stores', StoreController::class)->names('warehouse.stores');
        Route::post('stores/{id}/staff', [StoreController::class, 'storeStaff'])->name('warehouse.stores.staff.store');
        Route::delete('stores/staff/{id}', [StoreController::class, 'destroyStaff'])->name('warehouse.stores.staff.destroy');

        Route::controller(StockRequestController::class)->group(function () {
            Route::get('stock-requests', 'index')->name('warehouse.stock-requests.index');
            Route::get('stock-requests/{id}', 'show')->name('warehouse.stock-requests.show');
            Route::post('stock-requests/change-status', 'changeStatus')->name('warehouse.stock-requests.change-status');
            Route::post('stock-requests/verify-payment', 'verifyPayment')->name('warehouse.stock-requests.verify-payment');
            Route::post('stock-requests/purchase-in', 'purchaseIn')->name('warehouse.stock-requests.purchase-in');
        });

        // ===== STOCK CONTROL MODULE ROUTES (COMPLETE) =====
        Route::prefix('stock-control')->name('warehouse.stock-control.')->group(function () {

            // Stock Overview
            Route::get('overview', [StockControlController::class, 'overview'])->name('overview');
            Route::get('overview/data', [StockControlController::class, 'overviewData'])->name('overview.data');


            // Recall Stock - NEW 3-TAB STRUCTURE
            Route::get('recall', [RecallController::class, 'indexTabs'])->name('recall');
            Route::get('recall/my-requests', [RecallController::class, 'myRequests'])->name('recall.my-requests');
            Route::get('recall/store-requests', [RecallController::class, 'storeRequests'])->name('recall.store-requests');
            Route::get('recall/expiry-damage', [RecallController::class, 'expiryDamage'])->name('recall.expiry-damage');
            Route::get('recall/create', [RecallController::class, 'create'])->name('recall.create');
            Route::post('recall/store', [RecallController::class, 'store'])->name('recall.store');
            Route::get('recall/{recall}', [RecallController::class, 'show'])->name('recall.show');
            Route::post('recall/{recall}/approve', [RecallController::class, 'approve'])->name('recall.approve');
            Route::post('recall/{recall}/reject', [RecallController::class, 'reject'])->name('recall.reject');
            Route::post('recall/{recall}/dispatch', [RecallController::class, 'dispatch'])->name('recall.dispatch');
            Route::post('recall/{recall}/receive', [RecallController::class, 'receive'])->name('recall.receive');

            // Stock Valuation - NEW FULL IMPLEMENTATION
            Route::get('valuation', [StockControlController::class, 'valuation'])->name('valuation');
            Route::get('valuation/data', [StockControlController::class, 'valuationData'])->name('valuation.data');
            Route::get('valuation/stores', [StockControlController::class, 'storeValuation'])->name('valuation.stores');
            Route::get('valuation/store/{store}', [StockControlController::class, 'storeAnalytics'])->name('valuation.store-analytics');

            Route::get('valuation/product/{product}', [StockControlController::class, 'productAnalytics'])->name('valuation.product');

            // Min-Max Levels
            Route::resource('minmax', MinMaxController::class)->only(['index']);
            Route::get('minmax/data', [MinMaxController::class, 'data'])->name('minmax.data');
            Route::post('minmax', [MinMaxController::class, 'store'])->name('minmax.store');
            Route::put('minmax/{id}', [MinMaxController::class, 'update'])->name('minmax.update');

            // Rules (placeholder for future expansion)
            Route::get('rules', [StockControlController::class, 'rules'])->name('rules');
        });

        Route::resource('vendors', VendorController::class)->names('warehouse.vendors');
        Route::post('vendors/status', [VendorController::class, 'changeStatus'])->name('warehouse.vendors.status');
        Route::resource('purchase-orders', PurchaseOrderController::class)->names('warehouse.purchase-orders');
        Route::post('purchase-orders/{purchase_order}/mark-ordered', [PurchaseOrderController::class, 'markOrdered'])->name('warehouse.purchase-orders.mark-ordered');
        Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('warehouse.purchase-orders.receive');
        Route::get('purchase-orders/{purchase_order}/labels', [PurchaseOrderController::class, 'printLabels'])->name('warehouse.purchase-orders.labels');

        Route::controller(DiscrepancyController::class)->group(function () {
            Route::get('/discrepancy', 'index')->name('warehouse.discrepancy.index');
            Route::get('/discrepancy/transfer-issues', 'transferIssues')->name('warehouse.discrepancy.transfer-issues');
            Route::get('/discrepancy/store-returns', 'storeReturns')->name('warehouse.discrepancy.store-returns');
        });

        Route::controller(FinanceReportController::class)->group(function () {
            Route::get('/finance/overview', 'index')->name('warehouse.finance.index');
            Route::get('/finance/ledger', 'ledger')->name('warehouse.finance.ledger');
            Route::get('/finance/ledger/export', 'exportLedger')->name('warehouse.finance.ledger.export');
        });
    });
});
