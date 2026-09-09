<?php

use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\StoreController as SuperAdminStoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('super-admin.login');
});

/*
|--------------------------------------------------------------------------
| Super Admin Authentication & Protected Routes
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Guest Super Admin routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.submit');
    });

    // Protected Super Admin routes
    Route::middleware(['auth', 'super_admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');

        // Store Management Routes
        Route::get('/stores', [SuperAdminStoreController::class, 'index'])->name('stores.index');
        Route::get('/stores/create', [SuperAdminStoreController::class, 'create'])->name('stores.create');
        Route::post('/stores', [SuperAdminStoreController::class, 'store'])->name('stores.store');
        Route::get('/stores/{store}', [SuperAdminStoreController::class, 'show'])->name('stores.show');
        Route::get('/stores/{store}/edit', [SuperAdminStoreController::class, 'edit'])->name('stores.edit');
        Route::put('/stores/{store}', [SuperAdminStoreController::class, 'update'])->name('stores.update');
        Route::patch('/stores/{store}/status', [SuperAdminStoreController::class, 'updateStatus'])->name('stores.update-status');
    });
});
