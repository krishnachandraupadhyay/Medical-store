<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\BillingCycle;
use App\Enums\PlanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Subscription\StoreSubscriptionPlanRequest;
use App\Http\Requests\SuperAdmin\Subscription\UpdateSubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans.
     */
    public function index(Request $request): View
    {
        $query = SubscriptionPlan::query();

        // 1. Search filter
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Status filter
        $status = $request->input('status');
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // 3. Billing cycle filter
        $cycle = $request->input('cycle');
        if ($cycle !== null && $cycle !== '') {
            $query->where('billing_cycle', $cycle);
        }

        // Counts for filter pills
        $counts = [
            'all' => SubscriptionPlan::count(),
            'active' => SubscriptionPlan::where('status', PlanStatus::ACTIVE->value)->count(),
            'inactive' => SubscriptionPlan::where('status', PlanStatus::INACTIVE->value)->count(),
            'monthly' => SubscriptionPlan::where('billing_cycle', BillingCycle::MONTHLY->value)->count(),
            'yearly' => SubscriptionPlan::where('billing_cycle', BillingCycle::YEARLY->value)->count(),
        ];

        $plans = $query->orderBy('sort_order')->latest()->paginate(10)->withQueryString();

        return view('super-admin.subscriptions.plans.index', [
            'plans' => $plans,
            'counts' => $counts,
            'currentStatus' => $status,
            'currentCycle' => $cycle,
            'search' => $search,
            'supportedFeatures' => SubscriptionPlan::supportedFeatures(),
            'supportedLimits' => SubscriptionPlan::supportedLimits(),
        ]);
    }

    /**
     * Show the form for creating a new subscription plan.
     */
    public function create(): View
    {
        return view('super-admin.subscriptions.plans.create', [
            'supportedFeatures' => SubscriptionPlan::supportedFeatures(),
            'supportedLimits' => SubscriptionPlan::supportedLimits(),
            'billingCycles' => BillingCycle::cases(),
            'planStatuses' => PlanStatus::cases(),
        ]);
    }

    /**
     * Store a newly created subscription plan in storage.
     */
    public function store(StoreSubscriptionPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $slug = ! empty($data['slug'])
            ? SubscriptionPlan::generateUniqueSlug($data['slug'])
            : SubscriptionPlan::generateUniqueSlug($data['name']);

        $plan = SubscriptionPlan::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'price' => (float) $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'trial_days' => (int) $data['trial_days'],
            'status' => $data['status'],
            'limits' => $data['limits'] ?? [],
            'features' => $data['features'] ?? [],
            'is_popular' => (bool) ($data['is_popular'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::SUBSCRIPTION_PLANS,
            "Created subscription plan: {$plan->name} ({$plan->billing_cycle->value})",
            $plan,
            null,
            $plan->toArray()
        );

        return redirect()->route('super-admin.subscriptions.plans.show', $plan)
            ->with('status', "Subscription Plan [{$plan->name}] created successfully!");
    }

    /**
     * Display the specified subscription plan details.
     */
    public function show(SubscriptionPlan $plan): View
    {
        $plan->load(['creator', 'updater']);

        return view('super-admin.subscriptions.plans.show', [
            'plan' => $plan,
            'supportedFeatures' => SubscriptionPlan::supportedFeatures(),
            'supportedLimits' => SubscriptionPlan::supportedLimits(),
        ]);
    }

    /**
     * Show the form for editing the specified subscription plan.
     */
    public function edit(SubscriptionPlan $plan): View
    {
        return view('super-admin.subscriptions.plans.edit', [
            'plan' => $plan,
            'supportedFeatures' => SubscriptionPlan::supportedFeatures(),
            'supportedLimits' => SubscriptionPlan::supportedLimits(),
            'billingCycles' => BillingCycle::cases(),
            'planStatuses' => PlanStatus::cases(),
        ]);
    }

    /**
     * Update the specified subscription plan in storage.
     */
    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $plan): RedirectResponse
    {
        $data = $request->validated();
        $oldValues = $plan->toArray();

        $slug = ! empty($data['slug'])
            ? SubscriptionPlan::generateUniqueSlug($data['slug'], $plan->id)
            : SubscriptionPlan::generateUniqueSlug($data['name'], $plan->id);

        $plan->update([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'price' => (float) $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'trial_days' => (int) $data['trial_days'],
            'status' => $data['status'],
            'limits' => $data['limits'] ?? [],
            'features' => $data['features'] ?? [],
            'is_popular' => (bool) ($data['is_popular'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::SUBSCRIPTION_PLANS,
            "Updated subscription plan: {$plan->name}",
            $plan,
            $oldValues,
            $plan->toArray()
        );

        return redirect()->route('super-admin.subscriptions.plans.show', $plan)
            ->with('status', "Subscription Plan [{$plan->name}] updated successfully!");
    }

    /**
     * Toggle active/inactive status for a subscription plan.
     */
    public function updateStatus(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $oldStatus = $plan->status->value;
        $newStatus = PlanStatus::from($request->input('status'));

        $plan->update([
            'status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            $newStatus === PlanStatus::ACTIVE ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED,
            AuditModule::SUBSCRIPTION_PLANS,
            "Updated subscription plan [{$plan->name}] status from {$oldStatus} to {$newStatus->value}.",
            $plan,
            ['status' => $oldStatus],
            ['status' => $newStatus->value]
        );

        $actionText = $newStatus === PlanStatus::ACTIVE ? 'Activated' : 'Deactivated';

        return redirect()->back()
            ->with('status', "Subscription Plan [{$plan->name}] has been {$actionText} successfully.");
    }
}
