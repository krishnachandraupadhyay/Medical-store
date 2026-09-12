<?php

use App\Models\Store;
use App\Services\CurrentStoreContext;
use App\Services\SettingService;
use App\Services\SubscriptionAccessService;

if (! function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we assume you want to set an array of values.
     *
     * @param  string|array<string, mixed>|null  $key
     * @param  mixed  $default
     * @return mixed|SettingService
     */
    function setting($key = null, $default = null)
    {
        $service = app(SettingService::class);

        if (is_null($key)) {
            return $service;
        }

        if (is_array($key)) {
            $service->setMany($key, auth()->id());

            return null;
        }

        return $service->get($key, $default);
    }
}

if (! function_exists('current_store')) {
    /**
     * Get the current store context for the authenticated user.
     */
    function current_store(): ?Store
    {
        return app(CurrentStoreContext::class)->get();
    }
}

if (! function_exists('subscription_access')) {
    /**
     * Access the subscription and feature access service.
     */
    function subscription_access(): SubscriptionAccessService
    {
        return app(SubscriptionAccessService::class);
    }
}
