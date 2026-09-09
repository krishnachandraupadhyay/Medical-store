<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $totalStores = $hasStoresTable ? DB::table('stores')->count() : 0;
        $activeStores = $hasStoresTable ? DB::table('stores')->where('is_active', true)->count() : 0;
        $inactiveStores = $hasStoresTable ? DB::table('stores')->where('is_active', false)->count() : 0;

        // 2. Subscriptions & Revenue Metrics (safe placeholders for future modules)
        $hasSubscriptionsTable = Schema::hasTable('subscriptions');
        $activeSubscriptions = $hasSubscriptionsTable ? DB::table('subscriptions')->where('status', 'active')->count() : null;
        $expiringSubscriptions = $hasSubscriptionsTable ? DB::table('subscriptions')->where('status', 'expiring')->count() : null;

        $hasPaymentsTable = Schema::hasTable('payments');
        $totalRevenue = $hasPaymentsTable ? DB::table('payments')->where('status', 'completed')->sum('amount') : null;

        // 3. Recent Stores list
        $recentStores = $hasStoresTable
            ? DB::table('stores')->latest()->limit(5)->get()
            : collect([]);

        // 4. System Activities
        $hasAuditLogsTable = Schema::hasTable('audit_logs');
        $recentActivities = $hasAuditLogsTable
            ? DB::table('audit_logs')->latest()->limit(5)->get()
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
            'recent_stores' => $recentStores,
            'recent_activities' => $recentActivities,
        ];

        return view('super-admin.dashboard', [
            'admin' => $admin,
            'stats' => $stats,
        ]);
    }
}
