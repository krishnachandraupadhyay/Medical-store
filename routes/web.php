<?php

use App\Http\Controllers\Store\AuthController as StoreAuthController;
use App\Http\Controllers\Store\DashboardController as StoreDashboardController;
use App\Http\Controllers\SuperAdmin\AuditLogController as SuperAdminAuditLogController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\NotificationController;
use App\Http\Controllers\SuperAdmin\PaymentController as SuperAdminPaymentController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\SettingController as SuperAdminSettingController;
use App\Http\Controllers\SuperAdmin\StoreController as SuperAdminStoreController;
use App\Http\Controllers\SuperAdmin\StoreOwnerController as SuperAdminStoreOwnerController;
use App\Http\Controllers\SuperAdmin\StoreSubscriptionController as SuperAdminStoreSubscriptionController;
use App\Http\Controllers\SuperAdmin\SubscriptionPlanController as SuperAdminSubscriptionPlanController;
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

        // Store Owner Management Routes
        Route::get('/store-owners', [SuperAdminStoreOwnerController::class, 'index'])->name('store-owners.index');
        Route::get('/store-owners/create', [SuperAdminStoreOwnerController::class, 'create'])->name('store-owners.create');
        Route::post('/store-owners', [SuperAdminStoreOwnerController::class, 'store'])->name('store-owners.store');
        Route::get('/store-owners/{user}', [SuperAdminStoreOwnerController::class, 'show'])->name('store-owners.show');
        Route::get('/store-owners/{user}/edit', [SuperAdminStoreOwnerController::class, 'edit'])->name('store-owners.edit');
        Route::put('/store-owners/{user}', [SuperAdminStoreOwnerController::class, 'update'])->name('store-owners.update');
        Route::patch('/store-owners/{user}/status', [SuperAdminStoreOwnerController::class, 'updateStatus'])->name('store-owners.update-status');

        // Subscription Management Routes
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            // Subscription Plans
            Route::get('/plans', [SuperAdminSubscriptionPlanController::class, 'index'])->name('plans.index');
            Route::get('/plans/create', [SuperAdminSubscriptionPlanController::class, 'create'])->name('plans.create');
            Route::post('/plans', [SuperAdminSubscriptionPlanController::class, 'store'])->name('plans.store');
            Route::get('/plans/{plan}', [SuperAdminSubscriptionPlanController::class, 'show'])->name('plans.show');
            Route::get('/plans/{plan}/edit', [SuperAdminSubscriptionPlanController::class, 'edit'])->name('plans.edit');
            Route::put('/plans/{plan}', [SuperAdminSubscriptionPlanController::class, 'update'])->name('plans.update');
            Route::patch('/plans/{plan}/status', [SuperAdminSubscriptionPlanController::class, 'updateStatus'])->name('plans.update-status');

            // Store Subscriptions (Phase 6)
            Route::get('/stores', [SuperAdminStoreSubscriptionController::class, 'index'])->name('stores.index');
            Route::get('/stores/create', [SuperAdminStoreSubscriptionController::class, 'create'])->name('stores.create');
            Route::post('/stores', [SuperAdminStoreSubscriptionController::class, 'store'])->name('stores.store');
            Route::get('/stores/{subscription}', [SuperAdminStoreSubscriptionController::class, 'show'])->name('stores.show');
            Route::get('/stores/{subscription}/edit', [SuperAdminStoreSubscriptionController::class, 'edit'])->name('stores.edit');
            Route::put('/stores/{subscription}', [SuperAdminStoreSubscriptionController::class, 'update'])->name('stores.update');
            Route::patch('/stores/{subscription}/status', [SuperAdminStoreSubscriptionController::class, 'updateStatus'])->name('stores.update-status');
        });

        // Payment Management Routes (Phase 7)
        Route::get('/payments', [SuperAdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [SuperAdminPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [SuperAdminPaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}', [SuperAdminPaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/edit', [SuperAdminPaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [SuperAdminPaymentController::class, 'update'])->name('payments.update');
        Route::patch('/payments/{payment}/status', [SuperAdminPaymentController::class, 'updateStatus'])->name('payments.update-status');

        // Reports & Analytics Routes (Phase 8)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'overview'])->name('overview');
            Route::get('/overview', [ReportController::class, 'overview'])->name('overview.alias');
            Route::get('/stores', [ReportController::class, 'stores'])->name('stores');
            Route::get('/stores/export', [ReportController::class, 'exportStores'])->name('stores.export');
            Route::get('/subscriptions', [ReportController::class, 'subscriptions'])->name('subscriptions');
            Route::get('/subscriptions/export', [ReportController::class, 'exportSubscriptions'])->name('subscriptions.export');
            Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
            Route::get('/payments/export', [ReportController::class, 'exportPayments'])->name('payments.export');
            Route::get('/expiring-subscriptions', [ReportController::class, 'expiringSubscriptions'])->name('expiring-subscriptions');
        });

        // Notification Management Routes (Phase 9)
        Route::resource('notifications', NotificationController::class);
        Route::post('notifications/{notification}/cancel', [NotificationController::class, 'cancel'])->name('notifications.cancel');

        // System Settings Routes (Phase 10)
        Route::get('/settings', [SuperAdminSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SuperAdminSettingController::class, 'update'])->name('settings.update');

        // Audit Logs Routes (Phase 11)
        Route::get('/audit-logs', [SuperAdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [SuperAdminAuditLogController::class, 'show'])->name('audit-logs.show');
    });
});

/*
|--------------------------------------------------------------------------
| Store Owner Authentication & Protected Routes (Phase 12)
|--------------------------------------------------------------------------
*/
Route::prefix('store')->name('store.')->group(function () {
    // Guest Store Owner routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [StoreAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [StoreAuthController::class, 'login'])->name('login.submit');
    });

    // Protected Store Owner routes
    Route::middleware(['auth', 'store_owner'])->group(function () {
        Route::get('/dashboard', [StoreDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [StoreAuthController::class, 'logout'])->name('logout');
    });
});
