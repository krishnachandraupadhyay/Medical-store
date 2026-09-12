<?php

namespace App\Facades;

use App\Enums\SubscriptionStatus;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionAccessService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Subscription|null getActiveSubscription(?Store $store = null)
 * @method static bool hasActiveSubscription(?Store $store = null)
 * @method static SubscriptionPlan|null getCurrentPlan(?Store $store = null)
 * @method static SubscriptionStatus|null getSubscriptionStatus(?Store $store = null)
 * @method static bool hasFeature(?Store $store, string $feature)
 * @method static bool canUseFeature(?Store $store, string $feature)
 * @method static int getLimit(?Store $store, string $limitKey)
 * @method static bool isUnlimited(?Store $store, string $limitKey)
 * @method static int getUsage(?Store $store, string $limitKey)
 * @method static int remaining(?Store $store, string $limitKey)
 * @method static bool checkLimit(?Store $store, string $limitKey, int $additional = 1)
 * @method static void registerUsageResolver(string $limitKey, callable $resolver)
 * @method static void clearCache()
 *
 * @see SubscriptionAccessService
 */
class SubscriptionAccess extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SubscriptionAccessService::class;
    }
}
