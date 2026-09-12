<?php

namespace App\Http\Controllers\Store;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of notifications for the current store.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        $user = auth()->user();

        $query = Notification::forStore($store->id)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            });

        // Search
        if ($search = $request->input('search')) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%");
            });
        }

        // Type filter
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Priority filter
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Status filter (all, unread, read)
        $statusFilter = $request->input('status', 'all');
        if ($statusFilter === 'unread') {
            $query->unread();
        } elseif ($statusFilter === 'read') {
            $query->read();
        }

        // Counts for tab badges
        $totalCount = Notification::forStore($store->id)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })->count();

        $unreadCount = $this->notificationService->getUnreadCount($store, $user);
        $readCount = max(0, $totalCount - $unreadCount);

        $notifications = $query
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $notificationTypes = NotificationType::cases();
        $priorities = NotificationPriority::cases();

        return view('store.notifications.index', compact(
            'notifications',
            'totalCount',
            'unreadCount',
            'readCount',
            'notificationTypes',
            'priorities',
            'statusFilter'
        ));
    }

    /**
     * Mark notification as read and redirect to its action url (or back).
     */
    public function show(Notification $notification): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        // Tenant isolation
        if ($notification->store_id !== null && $notification->store_id !== $store->id) {
            abort(403, 'Unauthorized access to notification.');
        }

        $this->notificationService->markAsRead($notification, $store);

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return redirect()->route('store.notifications.index')
            ->with('success', 'Notification marked as read.');
    }

    /**
     * Get live unread notifications count for header badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $store = current_store();
        if (! $store) {
            return response()->json(['unread_count' => 0]);
        }

        $count = $this->notificationService->getUnreadCount($store, auth()->user());

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Get recent notifications and unread count for navbar dropdown.
     */
    public function dropdown(Request $request): JsonResponse
    {
        $store = current_store();
        if (! $store) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $user = auth()->user();
        $recent = $this->notificationService->getRecentNotifications($store, $user, 6);
        $unreadCount = $this->notificationService->getUnreadCount($store, $user);

        $items = $recent->map(function (Notification $notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type?->value ?? 'system',
                'priority' => $notification->priority?->value ?? 'normal',
                'icon' => $notification->type?->icon() ?? 'heroicon-o-bell',
                'badge_classes' => $notification->type?->badgeClasses() ?? 'bg-slate-100 text-slate-700',
                'is_unread' => $notification->isUnread(),
                'time_ago' => $notification->created_at ? $notification->created_at->diffForHumans() : '',
                'action_url' => route('store.notifications.show', $notification->id),
                'mark_read_url' => route('store.notifications.mark-read', $notification->id),
            ];
        });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $items,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        if ($notification->store_id !== null && $notification->store_id !== $store->id) {
            abort(403, 'Unauthorized access to notification.');
        }

        $this->notificationService->markAsRead($notification, $store);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $this->notificationService->getUnreadCount($store, auth()->user()),
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read for current store/user.
     */
    public function markAllAsRead(Request $request)
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');

        $updatedCount = $this->notificationService->markAllAsRead($store, auth()->user());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'marked_count' => $updatedCount,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', "All notifications marked as read ({$updatedCount} updated).");
    }

    /**
     * Show notification preferences page.
     */
    public function preferences(): View
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        $user = auth()->user();

        // Fetch user preferences
        $existingPreferences = NotificationPreference::where('store_id', $store->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            })
            ->pluck('is_enabled', 'notification_type')
            ->toArray();

        // Categorize notification types
        $categories = [
            'Inventory & Expiry Alerts' => [
                NotificationType::LOW_STOCK,
                NotificationType::OUT_OF_STOCK,
                NotificationType::EXPIRING_BATCH,
                NotificationType::EXPIRED_BATCH,
            ],
            'Receivables & Payables' => [
                NotificationType::CUSTOMER_OUTSTANDING,
                NotificationType::SUPPLIER_OUTSTANDING,
                NotificationType::CUSTOMER_PAYMENT,
                NotificationType::SUPPLIER_PAYMENT,
            ],
            'Transactions & Operations' => [
                NotificationType::SALE_COMPLETED,
                NotificationType::PURCHASE_COMPLETED,
                NotificationType::SALES_RETURN_COMPLETED,
                NotificationType::PURCHASE_RETURN_COMPLETED,
                NotificationType::EXPENSE_CREATED,
            ],
            'Subscriptions & System' => [
                NotificationType::SUBSCRIPTION_EXPIRING,
                NotificationType::SUBSCRIPTION_EXPIRED,
            ],
        ];

        return view('store.notifications.preferences', compact(
            'categories',
            'existingPreferences'
        ));
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 403, 'No active store associated.');
        $user = auth()->user();

        $submitted = $request->input('preferences', []);

        // All configurable types
        $allTypes = [
            NotificationType::LOW_STOCK->value,
            NotificationType::OUT_OF_STOCK->value,
            NotificationType::EXPIRING_BATCH->value,
            NotificationType::EXPIRED_BATCH->value,
            NotificationType::CUSTOMER_OUTSTANDING->value,
            NotificationType::SUPPLIER_OUTSTANDING->value,
            NotificationType::CUSTOMER_PAYMENT->value,
            NotificationType::SUPPLIER_PAYMENT->value,
            NotificationType::SALE_COMPLETED->value,
            NotificationType::PURCHASE_COMPLETED->value,
            NotificationType::SALES_RETURN_COMPLETED->value,
            NotificationType::PURCHASE_RETURN_COMPLETED->value,
            NotificationType::EXPENSE_CREATED->value,
            NotificationType::SUBSCRIPTION_EXPIRING->value,
            NotificationType::SUBSCRIPTION_EXPIRED->value,
        ];

        foreach ($allTypes as $type) {
            $isEnabled = isset($submitted[$type]) && $submitted[$type] == '1';

            NotificationPreference::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'user_id' => $user->id,
                    'notification_type' => $type,
                ],
                [
                    'is_enabled' => $isEnabled,
                ]
            );
        }

        return redirect()->route('store.notifications.preferences')
            ->with('success', 'Notification preferences updated successfully.');
    }
}
