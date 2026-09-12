<?php

namespace App\Http\Controllers\Store;

use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Store;
use App\Services\DashboardReportService;
use App\Services\OutstandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardReportService $dashboardReportService,
        protected OutstandingService $outstandingService
    ) {}

    /**
     * Display the Store Owner Dashboard with real scoped database data and business reporting aggregates.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $store = current_store() ?? $user->store;
        abort_unless($store, 403, 'No active store associated.');

        // 1. Subscription Context
        $activeSubscription = $store->activeSubscription()->with('plan')->first()
            ?? $store->latestSubscription()->with('plan')->first();

        if ($request->input('period') === 'custom') {
            $request->validate([
                'from_date' => ['required', 'date'],
                'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            ]);
        }

        // 2. Date Range Resolution
        $range = $this->dashboardReportService->resolveDateRange($request->all());

        // 3. Business Summary Cards
        $summaryCards = $this->dashboardReportService->getSummaryCards($store, $range);

        // 4. Trends (Sales, Purchases, Expenses)
        $trends = $this->dashboardReportService->getTrends($store, $range);

        // 5. Top Selling Medicines
        $topMedicines = $this->dashboardReportService->getTopSellingMedicines($store, $range, 5);

        // 6. Inventory Alerts
        $lowStock = $this->dashboardReportService->getLowStockMedicines($store, 5);
        $outOfStock = $this->dashboardReportService->getOutOfStockMedicines($store, 5);
        $expirySummary = $this->dashboardReportService->getExpirySummary($store, 5);

        // 7. Outstanding Receivables & Payables
        $topCustomersOutstanding = $this->outstandingService->getTopCustomerOutstanding($store, 5);
        $topSuppliersOutstanding = $this->outstandingService->getTopSupplierOutstanding($store, 5);

        // 8. Operational Payments Breakdown
        $paymentMethodsSummary = $this->dashboardReportService->getPaymentMethodSummary($store, $range);

        // 9. Recent Combined Transactions
        $recentTransactions = $this->dashboardReportService->getRecentTransactions($store, 8);

        // 10. Platform Notifications for this Store
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

        // 11. SaaS Subscription Payments History
        $recentPayments = $store->payments()
            ->with(['subscriptionPlan'])
            ->latest('payment_date')
            ->take(5)
            ->get();

        return view('store.dashboard', compact(
            'user',
            'store',
            'activeSubscription',
            'range',
            'summaryCards',
            'trends',
            'topMedicines',
            'lowStock',
            'outOfStock',
            'expirySummary',
            'topCustomersOutstanding',
            'topSuppliersOutstanding',
            'paymentMethodsSummary',
            'recentTransactions',
            'notifications',
            'recentPayments'
        ));
    }
}
