<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class SubscriptionAccessService
{
    /**
     * Cache for active subscriptions per store ID in request lifecycle.
     *
     * @var array<int, Subscription|false>
     */
    protected array $activeSubscriptionCache = [];

    /**
     * Registered callbacks to resolve real resource usage for limits.
     *
     * @var array<string, callable>
     */
    protected static array $usageResolvers = [];

    /**
     * Map of common feature key aliases to standardized platform feature keys.
     *
     * @var array<string, string>
     */
    protected static array $featureAliases = [
        'medicine' => 'medicine_management',
        'inventory' => 'inventory_management',
        'purchase' => 'purchase_management',
        'purchases' => 'purchase_management',
        'supplier' => 'supplier_management',
        'suppliers' => 'supplier_management',
        'customer' => 'customer_management',
        'sales' => 'sales_management',
        'billing' => 'sales_management',
        'staff' => 'staff_management',
        'expenses' => 'expense_management',
        'expense' => 'expense_management',
        'business_reports' => 'reports',
        'report' => 'reports',
        'sales_returns' => 'sales_return',
        'purchase_returns' => 'purchase_return',
        'advanced_inventory' => 'inventory_management',
        'advanced_inventory_control' => 'inventory_management',
        'advanced_pos' => 'pos',
        'barcode_billing' => 'pos',
    ];

    /**
     * Map of common limit key aliases.
     *
     * @var array<string, string>
     */
    protected static array $limitAliases = [
        'max_monthly_invoices' => 'max_invoices',
        'monthly_invoices' => 'max_invoices',
        'medicines' => 'max_medicines',
        'staff' => 'max_staff',
        'customers' => 'max_customers',
        'suppliers' => 'max_suppliers',
        'storage' => 'max_storage',
    ];

    public function __construct(
        protected CurrentStoreContext $storeContext
    ) {}

    /**
     * Resolve store parameter to Store model or fallback to current store context.
     */
    protected function resolveStore(?Store $store = null): ?Store
    {
        return $store ?? $this->storeContext->get();
    }

    /**
     * Standardize feature key.
     */
    public function normalizeFeatureKey(string $feature): string
    {
        $cleaned = strtolower(trim($feature));

        return self::$featureAliases[$cleaned] ?? $cleaned;
    }

    /**
     * Standardize limit key.
     */
    public function normalizeLimitKey(string $limitKey): string
    {
        $cleaned = strtolower(trim($limitKey));

        return self::$limitAliases[$cleaned] ?? $cleaned;
    }

    /**
     * Register a callback to dynamically calculate usage for a specific limit key.
     */
    public static function registerUsageResolver(string $limitKey, callable $resolver): void
    {
        $normalized = strtolower(trim($limitKey));
        $normalized = self::$limitAliases[$normalized] ?? $normalized;
        self::$usageResolvers[$normalized] = $resolver;
    }

    /**
     * Get the active subscription for the store.
     * Validates both status (active/trial) and real calendar date window (start_date <= today <= end_date).
     */
    public function getActiveSubscription(?Store $store = null): ?Subscription
    {
        $targetStore = $this->resolveStore($store);
        if (! $targetStore) {
            return null;
        }

        $storeId = $targetStore->id;

        if (array_key_exists($storeId, $this->activeSubscriptionCache)) {
            $cached = $this->activeSubscriptionCache[$storeId];

            return $cached === false ? null : $cached;
        }

        $today = Carbon::today()->toDateString();

        /** @var Subscription|null $sub */
        $sub = Subscription::with('plan')
            ->where('store_id', $storeId)
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest('id')
            ->first();

        $this->activeSubscriptionCache[$storeId] = $sub ?? false;

        return $sub;
    }

    /**
     * Check if the store currently has an active, valid subscription.
     */
    public function hasActiveSubscription(?Store $store = null): bool
    {
        return $this->getActiveSubscription($store) !== null;
    }

    /**
     * Get the active plan tier assigned to the store.
     */
    public function getCurrentPlan(?Store $store = null): ?SubscriptionPlan
    {
        return $this->getActiveSubscription($store)?->plan;
    }

    /**
     * Get the dynamically calculated effective status for the store's latest subscription.
     */
    public function getSubscriptionStatus(?Store $store = null): ?SubscriptionStatus
    {
        $targetStore = $this->resolveStore($store);
        if (! $targetStore) {
            return null;
        }

        $active = $this->getActiveSubscription($targetStore);
        if ($active) {
            return $active->status;
        }

        $latest = Subscription::where('store_id', $targetStore->id)->latest('id')->first();
        if (! $latest) {
            return null;
        }

        return $latest->effectiveStatus();
    }

    /**
     * Check if a specific feature is enabled in the store's active subscription plan.
     */
    public function hasFeature(?Store $store, string $feature): bool
    {
        $plan = $this->getCurrentPlan($store);
        if (! $plan) {
            return false;
        }

        $normalized = $this->normalizeFeatureKey($feature);

        return $plan->hasFeature($normalized);
    }

    /**
     * Check if a store has both an active subscription AND the requested feature.
     */
    public function canUseFeature(?Store $store, string $feature): bool
    {
        if (! $this->hasActiveSubscription($store)) {
            return false;
        }

        return $this->hasFeature($store, $feature);
    }

    /**
     * Authorize feature access or abort with 403.
     */
    public function authorizeFeature(string $feature, ?Store $store = null): void
    {
        $targetStore = $this->resolveStore($store);
        abort_unless($targetStore && $this->canUseFeature($targetStore, $feature), 403, "Feature '{$feature}' is not enabled for your active plan.");
    }

    /**
     * Get numerical quota limit for a given key. Returns -1 for unlimited.
     */
    public function getLimit(?Store $store, string $limitKey): int
    {
        $plan = $this->getCurrentPlan($store);
        if (! $plan) {
            return 0;
        }

        $normalized = $this->normalizeLimitKey($limitKey);

        return $plan->getLimit($normalized, 0);
    }

    /**
     * Check if a quota limit is unlimited (-1).
     */
    public function isUnlimited(?Store $store, string $limitKey): bool
    {
        return $this->getLimit($store, $limitKey) === -1;
    }

    /**
     * Get current resource usage for the specified store and limit key.
     */
    public function getUsage(?Store $store, string $limitKey): int
    {
        $targetStore = $this->resolveStore($store);
        if (! $targetStore) {
            return 0;
        }

        $normalized = $this->normalizeLimitKey($limitKey);

        if (isset(self::$usageResolvers[$normalized])) {
            return (int) call_user_func(self::$usageResolvers[$normalized], $targetStore);
        }

        return 0;
    }

    /**
     * Calculate remaining capacity for a quota limit. Returns -1 for unlimited.
     */
    public function remaining(?Store $store, string $limitKey): int
    {
        $limit = $this->getLimit($store, $limitKey);
        if ($limit === -1) {
            return -1;
        }

        $usage = $this->getUsage($store, $limitKey);

        return max(0, $limit - $usage);
    }

    /**
     * Check if the store is allowed to consume additional capacity under this limit.
     */
    public function checkLimit(?Store $store, string $limitKey, int $additional = 1): bool
    {
        $limit = $this->getLimit($store, $limitKey);
        if ($limit === -1) {
            return true;
        }

        $usage = $this->getUsage($store, $limitKey);

        return ($usage + $additional) <= $limit;
    }

    /**
     * Helper alias to check if a resource can be created under quota.
     */
    public function canCreateResource(string $limitKey, ?Store $store = null, int $additional = 1): bool
    {
        return $this->checkLimit($store, $limitKey, $additional);
    }

    /**
     * Clear memoized subscription cache.
     */
    public function clearCache(): void
    {
        $this->activeSubscriptionCache = [];
    }
}
