<?php

namespace App\Http\Middleware;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionPlan;
use App\Services\AuditLogger;
use App\Services\CurrentStoreContext;
use App\Services\SubscriptionAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionFeature
{
    public function __construct(
        protected CurrentStoreContext $storeContext,
        protected SubscriptionAccessService $accessService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = Auth::user();

        // 1. Super Admin retains unrestricted access across the system (Part 10)
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // 2. Resolve store safely via CurrentStoreContext (Tenant Isolation - Part 1 & 11)
        $store = $this->storeContext->get();
        if (! $store) {
            return $this->denyAccess($request, 'No active medical store associated with your account.', 'NO_STORE');
        }

        // 3. Check Subscription Status (Part 3)
        $activeSubscription = $this->accessService->getActiveSubscription($store);

        if (! $activeSubscription) {
            $effectiveStatus = $this->accessService->getSubscriptionStatus($store);

            $reasonCode = 'SUBSCRIPTION_INACTIVE';
            $message = 'Your subscription is currently inactive. Please activate your subscription to continue using this feature.';

            if ($effectiveStatus === SubscriptionStatus::EXPIRED) {
                $reasonCode = 'SUBSCRIPTION_EXPIRED';
                $message = 'Your subscription has expired. Please renew your subscription to continue using this feature.';
            } elseif ($effectiveStatus === SubscriptionStatus::SUSPENDED) {
                $reasonCode = 'SUBSCRIPTION_SUSPENDED';
                $message = 'Your store subscription is currently suspended. Please contact platform support.';
            } elseif ($effectiveStatus === SubscriptionStatus::CANCELLED) {
                $reasonCode = 'SUBSCRIPTION_CANCELLED';
                $message = 'Your store subscription was cancelled. Please subscribe to continue using this feature.';
            } elseif ($effectiveStatus === SubscriptionStatus::PENDING) {
                $reasonCode = 'SUBSCRIPTION_PENDING';
                $message = 'Your store subscription is pending approval or payment confirmation.';
            }

            AuditLogger::log(
                AuditAction::ACCESS_DENIED,
                AuditModule::STORE_SUBSCRIPTIONS,
                "Feature access denied for store [{$store->name}]: {$message} (Requested: {$feature})",
                $store,
                null,
                ['feature' => $feature, 'reason' => $reasonCode]
            );

            return $this->denyAccess($request, $message, $reasonCode, $feature);
        }

        // 4. Check Feature Entitlement (Part 5)
        $normalizedFeature = $this->accessService->normalizeFeatureKey($feature);

        if (! $this->accessService->hasFeature($store, $normalizedFeature)) {
            $plan = $this->accessService->getCurrentPlan($store);
            $planName = $plan ? $plan->name : 'Current Plan';
            $featureDef = SubscriptionPlan::supportedFeatures()[$normalizedFeature] ?? null;
            $featureName = $featureDef['name'] ?? ucwords(str_replace('_', ' ', $feature));

            $message = "The feature '{$featureName}' is not included in your {$planName}. Please upgrade your plan to unlock this capability.";

            AuditLogger::log(
                AuditAction::ACCESS_DENIED,
                AuditModule::SUBSCRIPTION_PLANS,
                "Feature access denied for store [{$store->name}]: Feature '{$feature}' is not included in plan '{$planName}'.",
                $store,
                null,
                ['feature' => $normalizedFeature, 'plan' => $planName]
            );

            return $this->denyAccess($request, $message, 'FEATURE_NOT_INCLUDED', $normalizedFeature);
        }

        return $next($request);
    }

    /**
     * Return structured denial response based on client expectation.
     */
    protected function denyAccess(Request $request, string $message, string $code, ?string $feature = null): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Access Denied',
                'message' => $message,
                'code' => $code,
                'feature' => $feature,
            ], 403);
        }

        return response()->view('store.subscription.denied', [
            'message' => $message,
            'code' => $code,
            'feature' => $feature,
            'store' => $this->storeContext->get(),
        ], 403);
    }
}
