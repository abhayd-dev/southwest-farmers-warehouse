<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Warehouse\Auth\LoginController;
use App\Http\Controllers\Warehouse\Auth\ForgotPasswordController;
use App\Http\Controllers\Warehouse\Auth\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Warehouse\ProductController;
use App\Http\Controllers\Warehouse\ProductOptionController;
use App\Http\Controllers\Warehouse\ProductCategoryController;
use App\Http\Controllers\Warehouse\ProductStockController;
use App\Http\Controllers\Warehouse\ProductSubcategoryController;
use App\Http\Controllers\Warehouse\RolePermissionController;
use App\Http\Controllers\Warehouse\StaffController;
use App\Http\Controllers\Warehouse\StockRequestController;
use App\Http\Controllers\Warehouse\StoreController;
use App\Http\Controllers\WarehouseController;

/*
|--------------------------------------------------------------------------
| Warehouse Routes
|--------------------------------------------------------------------------
*/

Route::get('/cc', function () {
    Artisan::call('optimize:clear');
    echo "Cache cleared..";
});

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
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
    Route::get('/warehouse/edit/{warehouse}', [WarehouseController::class, 'edit'])->name('warehouse.edit');
    Route::put('/warehouse/update/{warehouse}', [WarehouseController::class, 'update'])->name('warehouse.update');
    Route::post('/warehouse/update-status', [WarehouseController::class, 'updateStatus'])->name('warehouse.update-status');

    Route::prefix('warehouse')->group(function () {
        
        // Product Management (Isolated)
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

        // Stock Management
        Route::controller(ProductStockController::class)->group(function () {
            Route::get('stocks', 'index')->name('warehouse.stocks.index');
            Route::get('stocks/create', 'create')->name('warehouse.stocks.create');
            Route::post('stocks/store', 'store')->name('warehouse.stocks.store');
            Route::get('stocks/product-details/{product}', 'getProductDetails')->name('warehouse.stocks.product-details');
            Route::get('stocks/{product}/history', 'history')->name('warehouse.stocks.history');
            Route::get('stocks/adjust', 'adjust')->name('warehouse.stocks.adjust');
            Route::post('stocks/adjust', 'storeAdjustment')->name('warehouse.stocks.store-adjustment');
        });

        Route::resource('roles', RolePermissionController::class)->names('warehouse.roles');
        Route::post('staff/status', [StaffController::class, 'changeStatus'])->name('warehouse.staff.status');
        Route::resource('staff', StaffController::class)->names('warehouse.staff');

        Route::post('stores/update-status', [StoreController::class, 'updateStatus'])->name('warehouse.stores.update-status');
        Route::get('stores/{id}/analytics', [StoreController::class, 'analytics'])->name('warehouse.stores.analytics');
        Route::resource('stores', StoreController::class)->names('warehouse.stores');
        Route::post('stores/{id}/staff', [StoreController::class, 'storeStaff'])->name('warehouse.stores.staff.store');
        Route::delete('stores/staff/{id}', [StoreController::class, 'destroyStaff'])->name('warehouse.stores.staff.destroy');

        // Stock Requests
        Route::controller(StockRequestController::class)->group(function () {
            Route::get('stock-requests', 'index')->name('warehouse.stock-requests.index');
            Route::get('stock-requests/{id}', 'show')->name('warehouse.stock-requests.show');
            Route::post('stock-requests/{id}', 'update')->name('warehouse.stock-requests.update'); // Existing logic preserved
            
            // New Routes for Workflow
            Route::post('stock-requests/change-status', 'changeStatus')->name('warehouse.stock-requests.change-status');
            Route::post('stock-requests/verify-payment', 'verifyPayment')->name('warehouse.stock-requests.verify-payment');
            Route::post('stock-requests/purchase-in', 'purchaseIn')->name('warehouse.stock-requests.purchase-in');
        });
    });
});