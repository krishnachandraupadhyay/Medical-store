<?php

use App\Http\Controllers\Store\AuthController as StoreAuthController;
use App\Http\Controllers\Store\CustomerController;
use App\Http\Controllers\Store\DashboardController as StoreDashboardController;
use App\Http\Controllers\Store\ExpenseCategoryController;
use App\Http\Controllers\Store\ExpenseController;
use App\Http\Controllers\Store\InventoryController;
use App\Http\Controllers\Store\MedicineController;
use App\Http\Controllers\Store\NotificationController as StoreNotificationController;
use App\Http\Controllers\Store\OutstandingController;
use App\Http\Controllers\Store\PosController;
use App\Http\Controllers\Store\PurchaseController;
use App\Http\Controllers\Store\PurchaseReturnController;
use App\Http\Controllers\Store\ReportController as StoreReportController;
use App\Http\Controllers\Store\RoleController;
use App\Http\Controllers\Store\SaleController;
use App\Http\Controllers\Store\SalesReturnController;
use App\Http\Controllers\Store\SettingController as StoreSettingController;
use App\Http\Controllers\Store\StaffController;
use App\Http\Controllers\Store\StockCountController;
use App\Http\Controllers\Store\StockOperationController;
use App\Http\Controllers\Store\StorePaymentController;
use App\Http\Controllers\Store\SupplierController;
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
        Route::get('/dashboard', [StoreDashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');
        Route::post('/logout', [StoreAuthController::class, 'logout'])->name('logout');

        // Store Profile & Settings Routes (Phase 14)
        Route::get('/settings', [StoreSettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.view');
        Route::put('/settings/profile', [StoreSettingController::class, 'updateProfile'])->name('settings.update-profile')->middleware('permission:settings.edit');
        Route::post('/settings/logo', [StoreSettingController::class, 'updateLogo'])->name('settings.update-logo')->middleware('permission:settings.edit');
        Route::delete('/settings/logo', [StoreSettingController::class, 'removeLogo'])->name('settings.remove-logo')->middleware('permission:settings.edit');
        Route::put('/settings/account', [StoreSettingController::class, 'updateAccount'])->name('settings.update-account')->middleware('permission:settings.edit');
        Route::put('/settings/password', [StoreSettingController::class, 'updatePassword'])->name('settings.update-password')->middleware('permission:settings.edit');

        // Medicine Master Routes (Phase 16) - Guarded by Feature Access Control
        Route::middleware('feature:medicine_management')->prefix('medicines')->name('medicines.')->group(function () {
            Route::get('/', [MedicineController::class, 'index'])->name('index')->middleware('permission:medicines.view');
            Route::get('/create', [MedicineController::class, 'create'])->name('create')->middleware('permission:medicines.create');
            Route::post('/', [MedicineController::class, 'store'])->name('store')->middleware('permission:medicines.create');
            Route::get('/{medicine}', [MedicineController::class, 'show'])->name('show')->middleware('permission:medicines.view');
            Route::get('/{medicine}/edit', [MedicineController::class, 'edit'])->name('edit')->middleware('permission:medicines.edit');
            Route::put('/{medicine}', [MedicineController::class, 'update'])->name('update')->middleware('permission:medicines.edit');
            Route::patch('/{medicine}/status', [MedicineController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:medicines.edit');
            Route::delete('/{medicine}', [MedicineController::class, 'destroy'])->name('destroy')->middleware('permission:medicines.delete');
        });

        // Supplier Management Routes (Phase 18) - Guarded by Feature Access Control
        Route::middleware('feature:supplier_management')->prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index')->middleware('permission:suppliers.view');
            Route::get('/create', [SupplierController::class, 'create'])->name('create')->middleware('permission:suppliers.create');
            Route::post('/', [SupplierController::class, 'store'])->name('store')->middleware('permission:suppliers.create');
            Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show')->middleware('permission:suppliers.view');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit')->middleware('permission:suppliers.edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update')->middleware('permission:suppliers.edit');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy')->middleware('permission:suppliers.delete');
            // Supplier Notes (Phase 25)
            Route::post('/{supplier}/notes', [SupplierController::class, 'storeNote'])->name('notes.store')->middleware('permission:suppliers.edit');
            Route::patch('/{supplier}/notes/{note}/done', [SupplierController::class, 'markNoteFollowUpDone'])->name('notes.done')->middleware('permission:suppliers.edit');
            Route::delete('/{supplier}/notes/{note}', [SupplierController::class, 'destroyNote'])->name('notes.destroy')->middleware('permission:suppliers.edit');
        });

        // Batch & Inventory Management Routes (Phase 17) - Guarded by Feature Access Control
        Route::middleware('feature:inventory_management')->prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index')->middleware('permission:inventory.view');
            Route::get('/create', [InventoryController::class, 'create'])->name('create')->middleware('permission:inventory.adjust');
            Route::post('/', [InventoryController::class, 'store'])->name('store')->middleware('permission:inventory.adjust');
            Route::get('/history', [InventoryController::class, 'history'])->name('history')->middleware('permission:inventory.view');

            // Inventory Valuation (Phase 24)
            Route::get('/valuation', [StockOperationController::class, 'valuation'])->name('valuation')->middleware('permission:inventory.view');

            // Stock Counts / Reconciliation (Phase 24)
            Route::prefix('stock-counts')->name('stock-counts.')->middleware('permission:inventory.reconcile')->group(function () {
                Route::get('/', [StockCountController::class, 'index'])->name('index');
                Route::get('/create', [StockCountController::class, 'create'])->name('create');
                Route::post('/', [StockCountController::class, 'store'])->name('store');
                Route::get('/{stockCount}', [StockCountController::class, 'show'])->name('show');
                Route::post('/{stockCount}/items', [StockCountController::class, 'addItem'])->name('items.add');
                Route::delete('/{stockCount}/items/{item}', [StockCountController::class, 'removeItem'])->name('items.remove');
                Route::post('/{stockCount}/complete', [StockCountController::class, 'complete'])->name('complete');
                Route::post('/{stockCount}/cancel', [StockCountController::class, 'cancel'])->name('cancel');
            });

            // Damaged Stock Operations (Phase 24)
            Route::get('/damaged', [StockOperationController::class, 'damagedIndex'])->name('damaged.index')->middleware('permission:inventory.damage');
            Route::post('/damaged', [StockOperationController::class, 'damagedStore'])->name('damaged.store')->middleware('permission:inventory.damage');

            // Lost Stock Operations (Phase 24)
            Route::get('/lost', [StockOperationController::class, 'lostIndex'])->name('lost.index')->middleware('permission:inventory.loss');
            Route::post('/lost', [StockOperationController::class, 'lostStore'])->name('lost.store')->middleware('permission:inventory.loss');

            // Expired Stock Operations (Phase 24)
            Route::get('/expired', [StockOperationController::class, 'expiredIndex'])->name('expired.index')->middleware('permission:inventory.expiry');
            Route::post('/expired', [StockOperationController::class, 'expiredProcess'])->name('expired.process')->middleware('permission:inventory.expiry');

            // Batch Status Change (Phase 24)
            Route::post('/batches/{batch}/status', [StockOperationController::class, 'changeBatchStatus'])->name('batches.status')->middleware('permission:inventory.adjust');

            Route::get('/{batch}', [InventoryController::class, 'show'])->name('show')->middleware('permission:inventory.view');
            Route::get('/{batch}/edit', [InventoryController::class, 'edit'])->name('edit')->middleware('permission:inventory.adjust');
            Route::put('/{batch}', [InventoryController::class, 'update'])->name('update')->middleware('permission:inventory.adjust');
            Route::post('/{batch}/adjust', [InventoryController::class, 'adjust'])->name('adjust')->middleware('permission:inventory.adjust');
        });

        // Purchase Management Routes (Phase 18) - Guarded by Feature Access Control
        Route::middleware('feature:purchase_management')->prefix('purchases')->name('purchases.')->group(function () {
            Route::get('/', [PurchaseController::class, 'index'])->name('index')->middleware('permission:purchases.view');
            Route::get('/create', [PurchaseController::class, 'create'])->name('create')->middleware('permission:purchases.create');
            Route::post('/', [PurchaseController::class, 'store'])->name('store')->middleware('permission:purchases.create');
            Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show')->middleware('permission:purchases.view');
            Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit')->middleware('permission:purchases.edit');
            Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('update')->middleware('permission:purchases.edit');
            Route::post('/{purchase}/complete', [PurchaseController::class, 'complete'])->name('complete')->middleware('permission:purchases.complete');
            Route::post('/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel')->middleware('permission:purchases.cancel');
            Route::post('/{purchase}/record-payment', [StorePaymentController::class, 'recordPurchasePayment'])->name('record-payment')->middleware('permission:supplier_payments.create');
        });

        // Customer Management Routes (Phase 19 / Phase 28)
        Route::middleware('feature:customer_management')->prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index')->middleware('permission:customers.view');
            Route::get('/export', [CustomerController::class, 'export'])->name('export')->middleware('permission:customers.view');
            Route::get('/create', [CustomerController::class, 'create'])->name('create')->middleware('permission:customers.create');
            Route::post('/', [CustomerController::class, 'store'])->name('store')->middleware('permission:customers.create');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show')->middleware('permission:customers.view');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit')->middleware('permission:customers.edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update')->middleware('permission:customers.edit');
            Route::patch('/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:customers.edit');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy')->middleware('permission:customers.delete');
            // Customer Notes (Phase 25)
            Route::post('/{customer}/notes', [CustomerController::class, 'storeNote'])->name('notes.store')->middleware('permission:customers.edit');
            Route::patch('/{customer}/notes/{note}/done', [CustomerController::class, 'markNoteFollowUpDone'])->name('notes.done')->middleware('permission:customers.edit');
            Route::delete('/{customer}/notes/{note}', [CustomerController::class, 'destroyNote'])->name('notes.destroy')->middleware('permission:customers.edit');
        });

        // POS Routes (Phase 19 / Phase 27)
        Route::middleware(['feature:pos', 'permission:sales.create'])->prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('index');
            Route::get('/search-medicines', [PosController::class, 'searchMedicines'])->name('search-medicines');
            Route::get('/search-customers', [PosController::class, 'searchCustomers'])->name('search-customers');
            Route::get('/barcode', [PosController::class, 'barcodeSearch'])->name('barcode');
            Route::post('/quick-customer', [PosController::class, 'quickCustomer'])->name('quick-customer');
            Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
            Route::post('/hold', [PosController::class, 'holdBill'])->name('hold');
            Route::get('/held-bills', [PosController::class, 'heldBills'])->name('held-bills');
            Route::get('/resume/{sale}', [PosController::class, 'resumeBill'])->name('resume');
            Route::delete('/held/{sale}', [PosController::class, 'discardHeld'])->name('discard-held');
        });

        // Sales Management Routes (Phase 19)
        Route::middleware('feature:sales_management')->prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index')->middleware('permission:sales.view');
            Route::get('/{sale}', [SaleController::class, 'show'])->name('show')->middleware('permission:sales.view');
            Route::get('/{sale}/invoice', [SaleController::class, 'invoice'])->name('invoice')->middleware('permission:sales.view');
            Route::post('/{sale}/complete', [SaleController::class, 'complete'])->name('complete')->middleware('permission:sales.complete');
            Route::post('/{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel')->middleware('permission:sales.cancel');
            Route::post('/{sale}/record-payment', [SaleController::class, 'recordPayment'])->name('record-payment')->middleware('permission:customer_payments.create');
        });

        // Sales Return Routes (Phase 20 / Phase 31)
        Route::middleware(['feature:sales_return', 'permission:sales.return'])->prefix('sales-returns')->name('sales-returns.')->group(function () {
            Route::get('/', [SalesReturnController::class, 'index'])->name('index');
            Route::get('/create', [SalesReturnController::class, 'create'])->name('create');
            Route::post('/', [SalesReturnController::class, 'store'])->name('store');
            Route::get('/{salesReturn}', [SalesReturnController::class, 'show'])->name('show');
            Route::get('/{salesReturn}/receipt', [SalesReturnController::class, 'receipt'])->name('receipt');
        });

        // Purchase Return Routes (Phase 20)
        Route::middleware(['feature:purchase_return', 'permission:purchases.return'])->prefix('purchase-returns')->name('purchase-returns.')->group(function () {
            Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
            Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
            Route::get('/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('show');
        });

        // Expense Management Routes (Phase 21)
        Route::middleware('feature:expense_management')->group(function () {
            Route::prefix('expenses')->name('expenses.')->group(function () {
                Route::get('/', [ExpenseController::class, 'index'])->name('index')->middleware('permission:expenses.view');
                Route::get('/create', [ExpenseController::class, 'create'])->name('create')->middleware('permission:expenses.create');
                Route::post('/', [ExpenseController::class, 'store'])->name('store')->middleware('permission:expenses.create');
                Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show')->middleware('permission:expenses.view');
                Route::post('/{expense}/cancel', [ExpenseController::class, 'cancel'])->name('cancel')->middleware('permission:expenses.delete');
            });

            Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
                Route::get('/', [ExpenseCategoryController::class, 'index'])->name('index')->middleware('permission:expenses.view');
                Route::post('/', [ExpenseCategoryController::class, 'store'])->name('store')->middleware('permission:expenses.create');
            });
        });

        // Payments & Settlements Tracking (Phase 21)
        Route::prefix('payments')->name('payments.')->middleware('permission:customer_payments.view')->group(function () {
            Route::get('/', [StorePaymentController::class, 'index'])->name('index');
            Route::get('/{payment}', [StorePaymentController::class, 'show'])->name('show');
        });

        // Outstanding Amount Tracking (Phase 21)
        Route::prefix('outstanding')->name('outstanding.')->middleware('permission:reports.view')->group(function () {
            Route::get('/customers', [OutstandingController::class, 'customers'])->name('customers');
            Route::get('/suppliers', [OutstandingController::class, 'suppliers'])->name('suppliers');
        });

        // Business Reports Foundation (Phase 22) - Guarded by Feature Access Control
        Route::middleware(['feature:reports', 'permission:reports.view'])->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [StoreReportController::class, 'index'])->name('index');
            Route::get('/sales', [StoreReportController::class, 'sales'])->name('sales');
            Route::get('/purchases', [StoreReportController::class, 'purchases'])->name('purchases');
            Route::get('/sales-returns', [StoreReportController::class, 'salesReturns'])->name('sales-returns');
            Route::get('/purchase-returns', [StoreReportController::class, 'purchaseReturns'])->name('purchase-returns');
            Route::get('/expenses', [StoreReportController::class, 'expenses'])->name('expenses');
            Route::get('/payments', [StoreReportController::class, 'payments'])->name('payments');
            Route::get('/inventory', [StoreReportController::class, 'inventory'])->name('inventory');
            Route::get('/stock-movements', [StoreReportController::class, 'stockMovements'])->name('stock-movements');
            Route::get('/medicine-sales', [StoreReportController::class, 'medicineSales'])->name('medicine-sales');
        });

        // Notification Center & Reminder Automation Routes (Phase 23)
        Route::prefix('notifications')->name('notifications.')->middleware('permission:notifications.view')->group(function () {
            Route::get('/', [StoreNotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [StoreNotificationController::class, 'unreadCount'])->name('unread-count');
            Route::get('/dropdown', [StoreNotificationController::class, 'dropdown'])->name('dropdown');
            Route::post('/mark-all-read', [StoreNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::get('/preferences', [StoreNotificationController::class, 'preferences'])->name('preferences');
            Route::put('/preferences', [StoreNotificationController::class, 'updatePreferences'])->name('update-preferences');
            Route::get('/{notification}', [StoreNotificationController::class, 'show'])->name('show');
            Route::post('/{notification}/read', [StoreNotificationController::class, 'markAsRead'])->name('mark-read');
        });

        // Staff Management & Role-Based Access Control (Phase 26) - Guarded by Feature Access Control
        Route::middleware('feature:staff_management')->prefix('staff')->name('staff.')->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('index')->middleware('permission:staff.view');
            Route::get('/create', [StaffController::class, 'create'])->name('create')->middleware('permission:staff.create');
            Route::post('/', [StaffController::class, 'store'])->name('store')->middleware('permission:staff.create');
            Route::get('/{staff}', [StaffController::class, 'show'])->name('show')->middleware('permission:staff.view');
            Route::get('/{staff}/edit', [StaffController::class, 'edit'])->name('edit')->middleware('permission:staff.edit');
            Route::put('/{staff}', [StaffController::class, 'update'])->name('update')->middleware('permission:staff.edit');
            Route::patch('/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:staff.deactivate');
            Route::post('/{staff}/reset-password', [StaffController::class, 'resetPassword'])->name('reset-password')->middleware('permission:staff.edit');
        });

        // Store Roles & Permissions Management (Phase 26)
        Route::middleware('feature:staff_management')->prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:staff.permissions');
            Route::get('/create', [RoleController::class, 'create'])->name('create')->middleware('permission:staff.permissions');
            Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('permission:staff.permissions');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('permission:staff.permissions');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update')->middleware('permission:staff.permissions');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:staff.permissions');
        });
    });
});
