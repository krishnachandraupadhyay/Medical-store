<?php

namespace App\Providers;

use App\Facades\SubscriptionAccess;
use App\Listeners\NotificationListener;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\CurrentStoreContext;
use App\Services\SubscriptionAccessService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrentStoreContext::class);
        $this->app->singleton(SubscriptionAccessService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('hasFeature', function (string $feature) {
            return SubscriptionAccess::canUseFeature(null, $feature);
        });

        Blade::if('hasActiveSubscription', function () {
            return SubscriptionAccess::hasActiveSubscription();
        });

        // Register default resource quota usage resolvers
        SubscriptionAccessService::registerUsageResolver('max_medicines', function (Store $store) {
            return Medicine::where('store_id', $store->id)->where('status', 'active')->count();
        });

        SubscriptionAccessService::registerUsageResolver('max_suppliers', function (Store $store) {
            return Supplier::where('store_id', $store->id)->where('status', 'active')->count();
        });

        SubscriptionAccessService::registerUsageResolver('max_batches', function (Store $store) {
            return Batch::where('store_id', $store->id)->where('status', 'active')->count();
        });

        SubscriptionAccessService::registerUsageResolver('max_customers', function (Store $store) {
            return Customer::where('store_id', $store->id)->where('status', 'active')->count();
        });

        SubscriptionAccessService::registerUsageResolver('max_invoices', function (Store $store) {
            return Sale::where('store_id', $store->id)->count();
        });

        SubscriptionAccessService::registerUsageResolver('max_staff', function (Store $store) {
            return $store->staff()->where('is_active', true)->count();
        });

        Event::subscribe(NotificationListener::class);
    }
}
