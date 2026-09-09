<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Parse date range from request preset or custom inputs.
     *
     * @return array{from: ?Carbon, to: ?Carbon, preset: string}
     */
    protected function parseDateRange(Request $request, string $defaultPreset = 'this_month'): array
    {
        $preset = $request->get('date_range', $defaultPreset);
        $now = Carbon::now();
        $from = null;
        $to = null;

        switch ($preset) {
            case 'today':
                $from = $now->copy()->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $from = $now->copy()->subDay()->startOfDay();
                $to = $now->copy()->subDay()->endOfDay();
                break;
            case 'last_7_days':
                $from = $now->copy()->subDays(6)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'last_30_days':
                $from = $now->copy()->subDays(29)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'this_month':
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $from = $now->copy()->subMonth()->startOfMonth();
                $to = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfYear();
                break;
            case 'all_time':
                $from = null;
                $to = null;
                break;
            case 'custom':
            default:
                if ($request->filled('date_from')) {
                    $from = Carbon::parse($request->get('date_from'))->startOfDay();
                }
                if ($request->filled('date_to')) {
                    $to = Carbon::parse($request->get('date_to'))->endOfDay();
                }
                $preset = ($from || $to) ? 'custom' : $defaultPreset;
                if ($preset === 'this_month' && ! $from && ! $to) {
                    $from = $now->copy()->startOfMonth();
                    $to = $now->copy()->endOfMonth();
                }
                break;
        }

        return [
            'from' => $from,
            'to' => $to,
            'preset' => $preset,
        ];
    }

    /**
     * Reports Overview Dashboard.
     */
    public function overview(Request $request): View
    {
        $dates = $this->parseDateRange($request, 'this_month');
        $from = $dates['from'];
        $to = $dates['to'];
        $preset = $dates['preset'];

        // 1. Stores KPIs
        $newStoresCount = ($from && $to)
            ? Store::whereBetween('created_at', [$from, $to])->count()
            : Store::count();

        $totalStores = Store::count();
        $activeStores = Store::where('status', StoreStatus::ACTIVE)->count();
        $inactiveStores = Store::where('status', StoreStatus::INACTIVE)->count();
        $suspendedStores = Store::where('status', StoreStatus::SUSPENDED)->count();

        // 2. Store Owners KPIs
        $totalOwners = User::where('role', UserRole::STORE_OWNER)->count();
        $activeOwners = User::where('role', UserRole::STORE_OWNER)->where('is_active', true)->count();
        $inactiveOwners = User::where('role', UserRole::STORE_OWNER)->where('is_active', false)->count();

        // 3. Subscriptions KPIs
        $activeSubs = Subscription::where('status', SubscriptionStatus::ACTIVE)->count();
        $trialSubs = Subscription::where('status', SubscriptionStatus::TRIAL)->count();
        $expiredSubs = Subscription::where(function ($q) {
            $q->where('status', SubscriptionStatus::EXPIRED)
                ->orWhere(function ($sub) {
                    $sub->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
                        ->where('end_date', '<', Carbon::today()->toDateString());
                });
        })->count();
        $cancelledSubs = Subscription::where('status', SubscriptionStatus::CANCELLED)->count();
        $suspendedSubs = Subscription::where('status', SubscriptionStatus::SUSPENDED)->count();

        // 4. Payments & Revenue KPIs
        $paymentQuery = Payment::query();
        if ($from && $to) {
            $paymentQuery->whereBetween('payment_date', [$from, $to]);
        }

        $totalRevenue = (clone $paymentQuery)->where('status', PaymentStatus::PAID)->sum('amount');
        $paidAmount = (clone $paymentQuery)->where('status', PaymentStatus::PAID)->sum('amount');
        $pendingAmount = (clone $paymentQuery)->where('status', PaymentStatus::PENDING)->sum('amount');
        $failedAmount = (clone $paymentQuery)->where('status', PaymentStatus::FAILED)->sum('amount');
        $refundedAmount = (clone $paymentQuery)->where('status', PaymentStatus::REFUNDED)->sum('amount');

        // Revenue Periods
        $now = Carbon::now();
        $thisMonthRevenue = Payment::where('status', PaymentStatus::PAID)
            ->whereBetween('payment_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('amount');
        $lastMonthRevenue = Payment::where('status', PaymentStatus::PAID)
            ->whereBetween('payment_date', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])
            ->sum('amount');
        $thisYearRevenue = Payment::where('status', PaymentStatus::PAID)
            ->whereBetween('payment_date', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])
            ->sum('amount');

        // 5. Subscription Plans Distribution
        $plansDistribution = SubscriptionPlan::withCount(['subscriptions' => function ($q) {
            $q->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL]);
        }])->get(['id', 'name', 'price'])->map(function ($plan) {
            return [
                'name' => $plan->name,
                'count' => $plan->subscriptions_count,
                'price' => (float) $plan->price,
            ];
        });

        // 6. Store Growth Chart Data
        $growthLabels = [];
        $growthNewStores = [];
        $growthActiveStores = [];
        $chartPeriod = $request->get('chart_period', '30_days');

        $days = match ($chartPeriod) {
            '7_days' => 7,
            '6_months' => 180,
            '1_year' => 365,
            default => 30,
        };

        if ($days <= 30) {
            for ($i = $days - 1; $i >= 0; $i--) {
                $dayDate = Carbon::today()->subDays($i);
                $growthLabels[] = $dayDate->format('M d');
                $growthNewStores[] = Store::whereDate('created_at', $dayDate)->count();
                $growthActiveStores[] = Store::where('status', StoreStatus::ACTIVE)
                    ->whereDate('created_at', '<=', $dayDate)
                    ->count();
            }
        } else {
            $monthsCount = $days === 180 ? 6 : 12;
            for ($i = $monthsCount - 1; $i >= 0; $i--) {
                $monthDate = Carbon::today()->subMonths($i);
                $growthLabels[] = $monthDate->format('M Y');
                $growthNewStores[] = Store::whereYear('created_at', $monthDate->year)
                    ->whereMonth('created_at', $monthDate->month)
                    ->count();
                $growthActiveStores[] = Store::where('status', StoreStatus::ACTIVE)
                    ->whereDate('created_at', '<=', $monthDate->copy()->endOfMonth())
                    ->count();
            }
        }

        // 7. Revenue Chart Data
        $revenueLabels = [];
        $revenueValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $m = Carbon::today()->subMonths($i);
            $revenueLabels[] = $m->format('M Y');
            $revenueValues[] = (float) Payment::where('status', PaymentStatus::PAID)
                ->whereYear('payment_date', $m->year)
                ->whereMonth('payment_date', $m->month)
                ->sum('amount');
        }

        // 8. Expiring Subscriptions (Next 14 days)
        $expiringSubscriptions = Subscription::with(['store.owners', 'plan'])
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
            ->whereBetween('end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(14)->toDateString()])
            ->orderBy('end_date', 'asc')
            ->limit(5)
            ->get();

        return view('super-admin.reports.overview', compact(
            'totalStores',
            'activeStores',
            'inactiveStores',
            'suspendedStores',
            'newStoresCount',
            'totalOwners',
            'activeOwners',
            'inactiveOwners',
            'activeSubs',
            'trialSubs',
            'expiredSubs',
            'cancelledSubs',
            'suspendedSubs',
            'totalRevenue',
            'paidAmount',
            'pendingAmount',
            'failedAmount',
            'refundedAmount',
            'thisMonthRevenue',
            'lastMonthRevenue',
            'thisYearRevenue',
            'plansDistribution',
            'growthLabels',
            'growthNewStores',
            'growthActiveStores',
            'revenueLabels',
            'revenueValues',
            'expiringSubscriptions',
            'preset',
            'from',
            'to',
            'chartPeriod'
        ));
    }

    /**
     * Store Reports Listing.
     */
    public function stores(Request $request): View
    {
        $dates = $this->parseDateRange($request, 'all_time');
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status');
        $planId = $request->get('plan_id');
        $subStatus = $request->get('subscription_status');

        $query = Store::with(['owners', 'activeSubscription.plan', 'latestSubscription.plan'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhereHas('owners', function ($oq) use ($search) {
                        $oq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($status) && in_array($status, StoreStatus::values(), true)) {
            $query->where('status', $status);
        }

        if ($dates['from'] && $dates['to']) {
            $query->whereBetween('created_at', [$dates['from'], $dates['to']]);
        }

        if (! empty($planId)) {
            $query->whereHas('activeSubscription', fn ($q) => $q->where('subscription_plan_id', $planId));
        }

        if (! empty($subStatus)) {
            $query->whereHas('activeSubscription', fn ($q) => $q->where('status', $subStatus));
        }

        $stores = $query->paginate(15)->withQueryString();
        $plans = SubscriptionPlan::orderBy('name')->get(['id', 'name']);
        $storeStatuses = StoreStatus::cases();
        $subStatuses = SubscriptionStatus::cases();

        return view('super-admin.reports.stores', [
            'stores' => $stores,
            'plans' => $plans,
            'storeStatuses' => $storeStatuses,
            'subStatuses' => $subStatuses,
            'search' => $search,
            'status' => $status,
            'planId' => $planId,
            'subStatus' => $subStatus,
            'preset' => $dates['preset'],
            'dateFrom' => $dates['from']?->format('Y-m-d'),
            'dateTo' => $dates['to']?->format('Y-m-d'),
        ]);
    }

    /**
     * Subscription Reports Listing.
     */
    public function subscriptions(Request $request): View
    {
        $dates = $this->parseDateRange($request, 'all_time');
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status');
        $planId = $request->get('plan_id');

        $query = Subscription::with(['store.owners', 'plan', 'creator'])
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('store', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('owners', function ($oq) use ($search) {
                            $oq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                })->orWhereHas('plan', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($status) && in_array($status, SubscriptionStatus::values(), true)) {
            $query->where('status', $status);
        }

        if (! empty($planId)) {
            $query->where('subscription_plan_id', $planId);
        }

        if ($dates['from'] && $dates['to']) {
            $query->whereBetween('start_date', [$dates['from']->toDateString(), $dates['to']->toDateString()]);
        }

        $subscriptions = $query->paginate(15)->withQueryString();
        $plans = SubscriptionPlan::orderBy('name')->get(['id', 'name']);
        $statuses = SubscriptionStatus::cases();

        $counts = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', SubscriptionStatus::ACTIVE)->count(),
            'trial' => Subscription::where('status', SubscriptionStatus::TRIAL)->count(),
            'expired' => Subscription::where('status', SubscriptionStatus::EXPIRED)->count(),
            'cancelled' => Subscription::where('status', SubscriptionStatus::CANCELLED)->count(),
        ];

        return view('super-admin.reports.subscriptions', [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
            'statuses' => $statuses,
            'counts' => $counts,
            'search' => $search,
            'status' => $status,
            'planId' => $planId,
            'preset' => $dates['preset'],
            'dateFrom' => $dates['from']?->format('Y-m-d'),
            'dateTo' => $dates['to']?->format('Y-m-d'),
        ]);
    }

    /**
     * Payment & Revenue Reports Listing.
     */
    public function payments(Request $request): View
    {
        $dates = $this->parseDateRange($request, 'all_time');
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status');
        $method = $request->get('payment_method');
        $planId = $request->get('plan_id');
        $storeId = $request->get('store_id');

        $query = Payment::with(['store.owners', 'subscriptionPlan', 'subscription.plan', 'creator'])
            ->latest('payment_date');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($status) && in_array($status, PaymentStatus::values(), true)) {
            $query->where('status', $status);
        }

        if (! empty($method) && in_array($method, PaymentMethod::values(), true)) {
            $query->where('payment_method', $method);
        }

        if (! empty($planId)) {
            $query->where(function ($q) use ($planId) {
                $q->where('subscription_plan_id', $planId)
                    ->orWhereHas('subscription', fn ($sq) => $sq->where('subscription_plan_id', $planId));
            });
        }

        if (! empty($storeId)) {
            $query->where('store_id', $storeId);
        }

        if ($dates['from'] && $dates['to']) {
            $query->whereBetween('payment_date', [$dates['from'], $dates['to']]);
        }

        $payments = (clone $query)->paginate(15)->withQueryString();

        $stats = [
            'total_transactions' => (clone $query)->count(),
            'paid_count' => (clone $query)->where('status', PaymentStatus::PAID)->count(),
            'pending_count' => (clone $query)->where('status', PaymentStatus::PENDING)->count(),
            'failed_count' => (clone $query)->where('status', PaymentStatus::FAILED)->count(),
            'refunded_count' => (clone $query)->where('status', PaymentStatus::REFUNDED)->count(),
            'total_paid_amount' => (clone $query)->where('status', PaymentStatus::PAID)->sum('amount'),
            'total_refunded_amount' => (clone $query)->where('status', PaymentStatus::REFUNDED)->sum('amount'),
        ];

        $stores = Store::orderBy('name')->get(['id', 'name', 'code']);
        $plans = SubscriptionPlan::orderBy('name')->get(['id', 'name']);
        $paymentStatuses = PaymentStatus::cases();
        $paymentMethods = PaymentMethod::cases();

        return view('super-admin.reports.payments', [
            'payments' => $payments,
            'stats' => $stats,
            'stores' => $stores,
            'plans' => $plans,
            'paymentStatuses' => $paymentStatuses,
            'paymentMethods' => $paymentMethods,
            'search' => $search,
            'status' => $status,
            'method' => $method,
            'planId' => $planId,
            'storeId' => $storeId,
            'preset' => $dates['preset'],
            'dateFrom' => $dates['from']?->format('Y-m-d'),
            'dateTo' => $dates['to']?->format('Y-m-d'),
        ]);
    }

    /**
     * Dedicated Expiring Subscriptions Report.
     */
    public function expiringSubscriptions(Request $request): View
    {
        $days = (int) $request->get('days', 30);
        if (! in_array($days, [7, 15, 30], true)) {
            $days = 30;
        }

        $today = Carbon::today()->toDateString();
        $targetDate = Carbon::today()->addDays($days)->toDateString();

        $query = Subscription::with(['store.owners', 'plan', 'creator'])
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
            ->whereBetween('end_date', [$today, $targetDate])
            ->orderBy('end_date', 'asc');

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->whereHas('store', function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $expiringSubscriptions = $query->paginate(15)->withQueryString();

        $counts = [
            '7_days' => Subscription::whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
                ->whereBetween('end_date', [$today, Carbon::today()->addDays(7)->toDateString()])
                ->count(),
            '15_days' => Subscription::whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
                ->whereBetween('end_date', [$today, Carbon::today()->addDays(15)->toDateString()])
                ->count(),
            '30_days' => Subscription::whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
                ->whereBetween('end_date', [$today, Carbon::today()->addDays(30)->toDateString()])
                ->count(),
        ];

        return view('super-admin.reports.expiring-subscriptions', [
            'expiringSubscriptions' => $expiringSubscriptions,
            'days' => $days,
            'counts' => $counts,
            'search' => $search,
        ]);
    }

    /**
     * Export Stores Report as CSV.
     */
    public function exportStores(Request $request): StreamedResponse
    {
        $dates = $this->parseDateRange($request, 'all_time');
        $status = $request->get('status');
        $planId = $request->get('plan_id');

        $query = Store::with(['owners', 'activeSubscription.plan'])
            ->latest('id');

        if (! empty($status)) {
            $query->where('status', $status);
        }
        if (! empty($planId)) {
            $query->whereHas('activeSubscription', fn ($q) => $q->where('subscription_plan_id', $planId));
        }
        if ($dates['from'] && $dates['to']) {
            $query->whereBetween('created_at', [$dates['from'], $dates['to']]);
        }

        $filename = 'stores_report_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Store Code',
                'Store Name',
                'Owner Name',
                'Owner Email',
                'Owner Mobile',
                'City',
                'State',
                'Store Status',
                'Current Plan',
                'Subscription Status',
                'Subscription End Date',
                'Created At',
            ]);

            $query->chunk(100, function ($stores) use ($handle) {
                foreach ($stores as $store) {
                    $owner = $store->owners->first();
                    $sub = $store->activeSubscription;
                    fputcsv($handle, [
                        $store->code,
                        $store->name,
                        $owner?->name ?? 'Unassigned',
                        $owner?->email ?? 'N/A',
                        $owner?->mobile ?? 'N/A',
                        $store->city,
                        $store->state,
                        $store->status->label(),
                        $sub?->plan?->name ?? 'None',
                        $sub ? $sub->effectiveStatusLabel() : 'No Subscription',
                        $sub ? $sub->end_date->format('Y-m-d') : 'N/A',
                        $store->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Export Subscriptions Report as CSV.
     */
    public function exportSubscriptions(Request $request): StreamedResponse
    {
        $dates = $this->parseDateRange($request, 'all_time');
        $status = $request->get('status');
        $planId = $request->get('plan_id');

        $query = Subscription::with(['store.owners', 'plan', 'creator'])
            ->latest('id');

        if (! empty($status)) {
            $query->where('status', $status);
        }
        if (! empty($planId)) {
            $query->where('subscription_plan_id', $planId);
        }
        if ($dates['from'] && $dates['to']) {
            $query->whereBetween('start_date', [$dates['from']->toDateString(), $dates['to']->toDateString()]);
        }

        $filename = 'subscriptions_report_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Subscription ID',
                'Store Code',
                'Store Name',
                'Owner Name',
                'Plan Name',
                'Plan Price',
                'Billing Cycle',
                'Start Date',
                'End Date',
                'Status',
                'Created By',
                'Created At',
            ]);

            $query->chunk(100, function ($subscriptions) use ($handle) {
                foreach ($subscriptions as $sub) {
                    $owner = $sub->store?->owners->first();
                    fputcsv($handle, [
                        $sub->id,
                        $sub->store?->code ?? 'N/A',
                        $sub->store?->name ?? 'N/A',
                        $owner?->name ?? 'N/A',
                        $sub->plan?->name ?? 'N/A',
                        $sub->plan?->price ?? '0.00',
                        $sub->plan?->billing_cycle?->label() ?? 'N/A',
                        $sub->start_date->format('Y-m-d'),
                        $sub->end_date->format('Y-m-d'),
                        $sub->effectiveStatusLabel(),
                        $sub->creator?->name ?? 'Super Admin',
                        $sub->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Export Payments Report as CSV.
     */
    public function exportPayments(Request $request): StreamedResponse
    {
        $dates = $this->parseDateRange($request, 'all_time');
        $status = $request->get('status');
        $method = $request->get('payment_method');

        $query = Payment::with(['store.owners', 'subscriptionPlan', 'subscription.plan'])
            ->latest('payment_date');

        if (! empty($status)) {
            $query->where('status', $status);
        }
        if (! empty($method)) {
            $query->where('payment_method', $method);
        }
        if ($dates['from'] && $dates['to']) {
            $query->whereBetween('payment_date', [$dates['from'], $dates['to']]);
        }

        $filename = 'payments_report_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Transaction ID',
                'Store Code',
                'Store Name',
                'Owner Name',
                'Plan Name',
                'Amount',
                'Currency',
                'Payment Method',
                'Status',
                'Payment Date',
                'Notes',
            ]);

            $query->chunk(100, function ($payments) use ($handle) {
                foreach ($payments as $p) {
                    $owner = $p->store?->owners->first();
                    $plan = $p->subscriptionPlan ?? $p->subscription?->plan;
                    fputcsv($handle, [
                        $p->transaction_id,
                        $p->store?->code ?? 'N/A',
                        $p->store?->name ?? 'N/A',
                        $owner?->name ?? 'N/A',
                        $plan?->name ?? 'Standard',
                        $p->amount,
                        $p->currency,
                        $p->payment_method->label(),
                        $p->status->label(),
                        $p->payment_date->format('Y-m-d H:i:s'),
                        $p->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
