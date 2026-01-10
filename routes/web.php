<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Warehouse\Auth\LoginController;
use App\Http\Controllers\Warehouse\Auth\ForgotPasswordController;
use App\Http\Controllers\Warehouse\Auth\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarehouseController;

/*
|--------------------------------------------------------------------------
| Warehouse Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/', fn() => redirect()->route('login'));

    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::get('/dashboard', fn() => view('dashboard'))
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    Route::get('/warehouse', [WarehouseController::class, 'index'])
        ->name('warehouse.index');

    Route::get('/warehouse/edit/{warehouse}', [WarehouseController::class, 'edit'])
        ->name('warehouse.edit');

    Route::put('/warehouse/update/{warehouse}', [WarehouseController::class, 'update'])
        ->name('warehouse.update');

    Route::post('/warehouse/update-status', [WarehouseController::class, 'updateStatus'])
        ->name('warehouse.update-status');
});
