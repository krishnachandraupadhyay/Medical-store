<?php

namespace App\Services;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Enums\SubscriptionStatus;
use App\Models\Notification;
use App\Models\Store;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected BusinessAlertService $businessAlertService
    ) {}

    /**
     * Process all scheduled reminders and business alerts across active stores.
     *
     * @return array<string, int>
     */
    public function processScheduledReminders(): array
    {
        $totals = [
            'stores_processed' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'expiring_batch' => 0,
            'expired_batch' => 0,
            'customer_outstanding' => 0,
            'supplier_outstanding' => 0,
            'subscription_alerts' => 0,
            'scheduled_dispatched' => 0,
        ];

        // 1. Dispatch pending scheduled notifications
        $scheduledCount = Notification::where('status', NotificationStatus::SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->update([
                'status' => NotificationStatus::SENT,
                'sent_at' => now(),
            ]);

        $totals['scheduled_dispatched'] = $scheduledCount;

        // 2. Process active stores in chunks of 50
        Store::where('status', 'active')->chunk(50, function ($stores) use (&$totals) {
            foreach ($stores as $store) {
                $totals['stores_processed']++;

                try {
                    $alerts = $this->businessAlertService->checkAllStoreAlerts($store);
                    $totals['low_stock'] += $alerts['low_stock'];
                    $totals['out_of_stock'] += $alerts['out_of_stock'];
                    $totals['expiring_batch'] += $alerts['expiring_batch'];
                    $totals['expired_batch'] += $alerts['expired_batch'];
                    $totals['customer_outstanding'] += $alerts['customer_outstanding'];
                    $totals['supplier_outstanding'] += $alerts['supplier_outstanding'];

                    $subAlerts = $this->checkSubscriptionReminders($store);
                    $totals['subscription_alerts'] += $subAlerts;
                } catch (\Throwable $e) {
                    Log::error("Failed processing reminders for store {$store->id}: {$e->getMessage()}", [
                        'exception' => $e,
                    ]);
                }
            }
        });

        return $totals;
    }

    /**
     * Check subscription renewal reminders and expiration for a store.
     */
    public function checkSubscriptionReminders(Store $store): int
    {
        $subscription = Subscription::where('store_id', $store->id)
            ->latest('id')
            ->first();

        if (! $subscription || ! $subscription->end_date) {
            return 0;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($subscription->end_date)->startOfDay();
        $daysRemaining = $today->diffInDays($endDate, false);

        $count = 0;

        // If expired
        if ($daysRemaining < 0 || $subscription->status === SubscriptionStatus::EXPIRED) {
            $notification = $this->notificationService->notify([
                'store_id' => $store->id,
                'type' => NotificationType::SUBSCRIPTION_EXPIRED,
                'title' => 'Subscription Has Expired',
                'message' => "Your store subscription expired on {$endDate->format('d M Y')}. Please renew now to maintain uninterrupted access.",
                'priority' => NotificationPriority::CRITICAL,
                'reference_type' => Subscription::class,
                'reference_id' => $subscription->id,
                'alert_key' => "sub_expired:{$subscription->id}",
                'action_url' => route('store.settings.index'),
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'end_date' => $endDate->toDateString(),
                ],
            ]);

            if ($notification && $notification->wasRecentlyCreated) {
                $count++;
            }

            return $count;
        }

        // Milestones: 7 days, 3 days, 1 day
        $milestones = [7, 3, 1];
        foreach ($milestones as $milestone) {
            if ($daysRemaining == $milestone) {
                $priority = $milestone <= 1 ? NotificationPriority::CRITICAL : NotificationPriority::HIGH;
                $title = "Subscription Expiring in {$milestone} ".($milestone === 1 ? 'Day' : 'Days');

                $notification = $this->notificationService->notify([
                    'store_id' => $store->id,
                    'type' => NotificationType::SUBSCRIPTION_EXPIRING,
                    'title' => $title,
                    'message' => "Your current subscription expires on {$endDate->format('d M Y')} ({$milestone} ".($milestone === 1 ? 'day' : 'days').' left). Renew today to avoid service disruption.',
                    'priority' => $priority,
                    'reference_type' => Subscription::class,
                    'reference_id' => $subscription->id,
                    'alert_key' => "sub_expiring:{$subscription->id}:{$milestone}d",
                    'action_url' => route('store.settings.index'),
                    'metadata' => [
                        'subscription_id' => $subscription->id,
                        'days_remaining' => $milestone,
                        'end_date' => $endDate->toDateString(),
                    ],
                ]);

                if ($notification && $notification->wasRecentlyCreated) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
