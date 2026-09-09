<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Super Admin Dashboard with real-time system metrics.
     */
    public function index(Request $request): View
    {
        $admin = Auth::user();

        // 1. Store & Owner Metrics
        $totalStoreOwners = User::where('role', UserRole::STORE_OWNER)->count();
        $activeStoreOwners = User::where('role', UserRole::STORE_OWNER)->where('is_active', true)->count();
        $inactiveStoreOwners = User::where('role', UserRole::STORE_OWNER)->where('is_active', false)->count();
        $totalSuperAdmins = User::where('role', UserRole::SUPER_ADMIN)->count();

        // Safe dynamic check if 'stores' table exists in database
        $hasStoresTable = Schema::hasTable('stores');
        $totalStores = $hasStoresTable ? Store::count() : 0;
        $activeStores = $hasStoresTable ? Store::where('is_active', true)->count() : 0;
        $inactiveStores = $hasStoresTable ? Store::where('is_active', false)->count() : 0;

        // 2. Subscriptions & Revenue Metrics
        $hasSubscriptionsTable = Schema::hasTable('subscriptions');
        $activeSubscriptions = $hasSubscriptionsTable
            ? Subscription::whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
                ->where('end_date', '>=', Carbon::today()->toDateString())
                ->count()
            : null;

        $expiringSubscriptions = $hasSubscriptionsTable
            ? Subscription::whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
                ->whereBetween('end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(7)->toDateString()])
                ->count()
            : null;

        $hasPaymentsTable = Schema::hasTable('payments');
        $totalRevenue = $hasPaymentsTable ? (float) Payment::where('status', PaymentStatus::PAID->value)->sum('amount') : 0;
        $paidPaymentsCount = $hasPaymentsTable ? Payment::where('status', PaymentStatus::PAID->value)->count() : 0;
        $pendingPaymentsCount = $hasPaymentsTable ? Payment::where('status', PaymentStatus::PENDING->value)->count() : 0;

        // 3. Recent Stores list
        $recentStores = $hasStoresTable
            ? Store::with('owners')->latest()->limit(5)->get()
            : collect([]);

        // 4. System Activities (Real Audit Logs)
        $hasAuditLogsTable = Schema::hasTable('audit_logs');
        $recentActivities = $hasAuditLogsTable
            ? AuditLog::with('user')->latest('id')->limit(5)->get()
            : collect([]);

        $stats = [
            'total_stores' => $totalStores,
            'active_stores' => $activeStores,
            'inactive_stores' => $inactiveStores,
            'has_stores_table' => $hasStoresTable,
            'total_store_owners' => $totalStoreOwners,
            'active_store_owners' => $activeStoreOwners,
            'inactive_store_owners' => $inactiveStoreOwners,
            'total_super_admins' => $totalSuperAdmins,
            'active_subscriptions' => $activeSubscriptions,
            'expiring_subscriptions' => $expiringSubscriptions,
            'total_revenue' => $totalRevenue,
            'paid_payments_count' => $paidPaymentsCount,
            'pending_payments_count' => $pendingPaymentsCount,
            'recent_stores' => $recentStores,
            'recent_activities' => $recentActivities,
        ];

        return view('super-admin.dashboard', [
            'admin' => $admin,
            'stats' => $stats,
        ]);
    }
}
