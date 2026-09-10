<?php

namespace App\Http\Controllers\Store;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Store Owner Dashboard with real scoped database data.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $store = $user->store;

        // 1. Current Subscription (Active or Latest)
        $activeSubscription = $store->activeSubscription()->with('plan')->first()
            ?? $store->latestSubscription()->with('plan')->first();

        // 2. Real Store Metrics
        $totalStaff = $store->users()->where('role', UserRole::STORE_STAFF)->count();
        $totalUsers = $store->users()->count();
        $totalPaidInvoices = $store->payments()->where('status', PaymentStatus::PAID)->count();
        $totalPaidRevenue = $store->payments()->where('status', PaymentStatus::PAID)->sum('amount');

        // 3. Real Recent Payment History for this Store
        $recentPayments = $store->payments()
            ->with(['subscriptionPlan'])
            ->latest('payment_date')
            ->take(5)
            ->get();

        // 4. Real Targeted Platform Notifications for this Store
        $notifications = Notification::where(function ($q) use ($store) {
            $q->where('target_type', NotificationTargetType::ALL_STORES)
                ->orWhere(function ($sub) use ($store) {
                    $sub->where('target_type', NotificationTargetType::SPECIFIC_STORE)
                        ->where('store_id', $store->id);
                });
        })
            ->where('status', NotificationStatus::SENT)
            ->latest('created_at')
            ->take(5)
            ->get();

        // 5. Real Tenant Activity Logs
        $recentActivities = AuditLog::where(function ($q) use ($user, $store) {
            $q->where('user_id', $user->id)
                ->orWhere(function ($sub) use ($store) {
                    $sub->where('subject_type', Store::class)->where('subject_id', $store->id);
                });
        })
            ->latest('id')
            ->take(6)
            ->get();

        return view('store.dashboard', compact(
            'user',
            'store',
            'activeSubscription',
            'totalStaff',
            'totalUsers',
            'totalPaidInvoices',
            'totalPaidRevenue',
            'recentPayments',
            'notifications',
            'recentActivities'
        ));
    }
}
