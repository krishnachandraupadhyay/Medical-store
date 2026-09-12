<?php

namespace App\Services;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create and dispatch an in-app notification.
     *
     * @param  array{
     *     store_id: int,
     *     user_id?: int|null,
     *     type: NotificationType|string,
     *     title: string,
     *     message: string,
     *     priority?: NotificationPriority|string|null,
     *     action_url?: string|null,
     *     reference_type?: string|null,
     *     reference_id?: int|string|null,
     *     alert_key?: string|null,
     *     metadata?: array|null
     * }  $data
     */
    public function notify(array $data): ?Notification
    {
        $storeId = (int) $data['store_id'];
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;

        $type = $data['type'] instanceof NotificationType
            ? $data['type']
            : NotificationType::tryFrom((string) $data['type']) ?? NotificationType::SYSTEM;

        $typeValue = $type->value;

        // Check preferences
        if (! NotificationPreference::isEnabled($storeId, $userId, $typeValue)) {
            Log::info("Notification skipped due to user/store preference: store_id={$storeId}, type={$typeValue}");

            return null;
        }

        // Deduplication check via alert_key
        $alertKey = $data['alert_key'] ?? null;
        if ($alertKey) {
            $existing = Notification::where('store_id', $storeId)
                ->where('alert_key', $alertKey)
                ->unread()
                ->first();

            if ($existing) {
                return $existing;
            }

            // For milestone / unique alert keys (e.g., sub_expired, milestone alerts), check if already sent regardless of read status
            if (str_starts_with($alertKey, 'sub_') || str_starts_with($alertKey, 'event:') || str_ends_with($alertKey, 'd')) {
                $anyExisting = Notification::where('store_id', $storeId)
                    ->where('alert_key', $alertKey)
                    ->first();

                if ($anyExisting) {
                    return $anyExisting;
                }
            }
        }

        // Determine priority
        $priority = $data['priority'] ?? null;
        if (! $priority) {
            $priority = $type->defaultPriority();
        } elseif (is_string($priority)) {
            $priority = NotificationPriority::tryFrom($priority) ?? NotificationPriority::NORMAL;
        }

        return Notification::create([
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $type,
            'priority' => $priority,
            'target_type' => NotificationTargetType::SPECIFIC_STORE,
            'store_id' => $storeId,
            'user_id' => $userId,
            'status' => NotificationStatus::SENT,
            'read_at' => null,
            'sent_at' => now(),
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'alert_key' => $alertKey,
            'metadata' => $data['metadata'] ?? null,
            'created_by' => $userId ?? auth()->id(),
            'updated_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Get unread notifications count for a store and optional user.
     */
    public function getUnreadCount(Store $store, ?User $user = null): int
    {
        return Notification::forStore($store->id)
            ->unread()
            ->when($user, function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                        ->orWhere('user_id', $user->id);
                });
            })
            ->count();
    }

    /**
     * Get recent notifications for dropdown or quick view.
     */
    public function getRecentNotifications(Store $store, ?User $user = null, int $limit = 5): Collection
    {
        return Notification::forStore($store->id)
            ->when($user, function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                        ->orWhere('user_id', $user->id);
                });
            })
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification, Store $store): bool
    {
        // Tenant security check
        if ($notification->store_id !== null && $notification->store_id !== $store->id) {
            return false;
        }

        $notification->markAsRead();

        return true;
    }

    /**
     * Mark all notifications as read for a store and optional user.
     */
    public function markAllAsRead(Store $store, ?User $user = null): int
    {
        return Notification::forStore($store->id)
            ->unread()
            ->when($user, function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('user_id')
                        ->orWhere('user_id', $user->id);
                });
            })
            ->update(['read_at' => now()]);
    }
}
