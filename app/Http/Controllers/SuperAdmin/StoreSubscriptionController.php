<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\PlanStatus;
use App\Enums\StoreStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Subscription\StoreStoreSubscriptionRequest;
use App\Http\Requests\SuperAdmin\Subscription\UpdateStoreSubscriptionRequest;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSubscriptionController extends Controller
{
    /**
     * Display a listing of store subscriptions.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $status = strtolower(trim((string) $request->get('status', '')));
        $planId = $request->get('plan_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Subscription::with(['store.owners', 'plan', 'creator'])
            ->latest('id');

        // Search Filter (Store Name, Store Code, Owner Name, Owner Email)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('store', function ($storeQuery) use ($search) {
                    $storeQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('owners', function ($ownerQuery) use ($search) {
                            $ownerQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                })->orWhereHas('plan', function ($planQuery) use ($search) {
                    $planQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Status Filter
        if ($status !== '') {
            if ($status === 'expiring_soon') {
                $query->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
                    ->whereBetween('end_date', [Carbon::today()->toDateString(), Carbon::today()->addDays(14)->toDateString()]);
            } elseif ($status === 'expired') {
                $query->where(function ($q) {
                    $q->where('status', SubscriptionStatus::EXPIRED->value)
                        ->orWhere(function ($sub) {
                            $sub->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
                                ->where('end_date', '<', Carbon::today()->toDateString());
                        });
                });
            } else {
                $query->where('status', $status);
            }
        }

        // Plan Filter
        if (! empty($planId)) {
            $query->where('subscription_plan_id', $planId);
        }

        // Date Range Filter
        if (! empty($dateFrom)) {
            $query->where('start_date', '>=', $dateFrom);
        }
        if (! empty($dateTo)) {
            $query->where('end_date', '<=', $dateTo);
        }

        $subscriptions = $query->paginate(10)->withQueryString();

        // Metrics counters
        $counts = [
            'all' => Subscription::count(),
            'active' => Subscription::where('status', SubscriptionStatus::ACTIVE)->where('end_date', '>=', Carbon::today())->count(),
            'trial' => Subscription::where('status', SubscriptionStatus::TRIAL)->where('end_date', '>=', Carbon::today())->count(),
            'expired' => Subscription::where('status', SubscriptionStatus::EXPIRED)->orWhere(function ($q) {
                $q->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
                    ->where('end_date', '<', Carbon::today());
            })->count(),
            'cancelled' => Subscription::where('status', SubscriptionStatus::CANCELLED)->count(),
            'suspended' => Subscription::where('status', SubscriptionStatus::SUSPENDED)->count(),
        ];

        $plans = SubscriptionPlan::orderBy('name')->get(['id', 'name', 'price', 'billing_cycle']);

        return view('super-admin.subscriptions.stores.index', compact(
            'subscriptions',
            'counts',
            'plans',
            'search',
            'status',
            'planId',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for assigning a new subscription to a medical store.
     */
    public function create(Request $request): View
    {
        $selectedStoreId = $request->get('store_id');

        $stores = Store::where('status', StoreStatus::ACTIVE)
            ->with(['owners', 'activeSubscription.plan'])
            ->orderBy('name')
            ->get();

        $plans = SubscriptionPlan::where('status', PlanStatus::ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('super-admin.subscriptions.stores.create', compact('stores', 'plans', 'selectedStoreId'));
    }

    /**
     * Store a newly assigned subscription in storage.
     */
    public function store(StoreStoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $storeId = (int) $data['store_id'];

        // Business Rule: Check for existing active/trial subscriptions for this store
        $existingActive = Subscription::where('store_id', $storeId)
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
            ->where('end_date', '>=', Carbon::today()->toDateString())
            ->first();

        // If an active subscription already exists and supersede was requested/handled
        if ($existingActive) {
            // Expire / supersede previous active subscription cleanly in history
            $existingActive->update([
                'status' => SubscriptionStatus::CANCELLED,
                'notes' => trim($existingActive->notes."\n[Super Admin]: Superseded by new subscription on ".now()->format('d M Y, h:i A')),
                'updated_by' => auth()->id(),
            ]);
        }

        $subscription = Subscription::create([
            'store_id' => $storeId,
            'subscription_plan_id' => $data['subscription_plan_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            AuditAction::ASSIGNED,
            AuditModule::STORE_SUBSCRIPTIONS,
            "Assigned subscription plan to store: {$subscription->store->name}",
            $subscription,
            null,
            $subscription->toArray()
        );

        return redirect()
            ->route('super-admin.subscriptions.stores.show', $subscription)
            ->with('success', 'Subscription plan assigned successfully to medical store.');
    }

    /**
     * Display the specified subscription details and store history.
     */
    public function show(Subscription $subscription): View
    {
        $subscription->load(['store.owners', 'plan', 'creator', 'updater']);

        // Historical subscriptions for the same store
        $history = Subscription::where('store_id', $subscription->store_id)
            ->where('id', '!=', $subscription->id)
            ->with(['plan', 'creator'])
            ->orderBy('id', 'desc')
            ->get();

        return view('super-admin.subscriptions.stores.show', compact('subscription', 'history'));
    }

    /**
     * Show the form for editing the subscription.
     */
    public function edit(Subscription $subscription): View
    {
        $subscription->load(['store.owners', 'plan']);

        $plans = SubscriptionPlan::where('status', PlanStatus::ACTIVE)
            ->orWhere('id', $subscription->subscription_plan_id)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('super-admin.subscriptions.stores.edit', compact('subscription', 'plans'));
    }

    /**
     * Update the specified subscription in storage.
     */
    public function update(UpdateStoreSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validated();
        $oldValues = $subscription->toArray();

        $subscription->update([
            'subscription_plan_id' => $data['subscription_plan_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'trial_ends_at' => $data['trial_ends_at'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORE_SUBSCRIPTIONS,
            "Updated subscription for store: {$subscription->store->name}",
            $subscription,
            $oldValues,
            $subscription->toArray()
        );

        return redirect()
            ->route('super-admin.subscriptions.stores.show', $subscription)
            ->with('success', 'Store subscription updated successfully.');
    }

    /**
     * Update status (Cancel, Suspend, Activate) of a subscription.
     */
    public function updateStatus(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:active,trial,expired,cancelled,suspended'],
        ]);

        $oldStatus = $subscription->status->value;
        $newStatus = SubscriptionStatus::from($request->string('status')->value());

        $subscription->update([
            'status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        $action = match ($newStatus) {
            SubscriptionStatus::ACTIVE => AuditAction::ACTIVATED,
            SubscriptionStatus::CANCELLED => AuditAction::CANCELLED,
            SubscriptionStatus::SUSPENDED => AuditAction::SUSPENDED,
            default => AuditAction::STATUS_CHANGED,
        };

        AuditLogger::log(
            $action,
            AuditModule::STORE_SUBSCRIPTIONS,
            "Updated subscription status for store: {$subscription->store->name} to {$newStatus->value}.",
            $subscription,
            ['status' => $oldStatus],
            ['status' => $newStatus->value]
        );

        $label = $newStatus->label();

        return back()->with('success', "Subscription status updated to {$label}.");
    }
}
