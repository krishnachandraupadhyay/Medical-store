<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\NotificationPriority;
use App\Enums\NotificationStatus;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Notification\StoreNotificationRequest;
use App\Http\Requests\SuperAdmin\Notification\UpdateNotificationRequest;
use App\Models\Notification;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of platform notifications.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $type = $request->get('type');
        $priority = $request->get('priority');
        $status = $request->get('status');
        $targetType = $request->get('target_type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Notification::with(['store', 'creator'])
            ->latest('id');

        // Search Filter (Title, Message)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        // Type Filter
        if (! empty($type) && in_array($type, NotificationType::values(), true)) {
            $query->where('type', $type);
        }

        // Priority Filter
        if (! empty($priority) && in_array($priority, NotificationPriority::values(), true)) {
            $query->where('priority', $priority);
        }

        // Status Filter
        if (! empty($status) && in_array($status, NotificationStatus::values(), true)) {
            $query->where('status', $status);
        }

        // Target Type Filter
        if (! empty($targetType) && in_array($targetType, NotificationTargetType::values(), true)) {
            $query->where('target_type', $targetType);
        }

        // Date Filter (Created / Sent)
        if (! empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (! empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $notifications = $query->paginate(15)->withQueryString();

        // Summary Statistics
        $stats = [
            'total' => Notification::count(),
            'sent' => Notification::where('status', NotificationStatus::SENT)->count(),
            'scheduled' => Notification::where('status', NotificationStatus::SCHEDULED)->count(),
            'draft' => Notification::where('status', NotificationStatus::DRAFT)->count(),
            'cancelled' => Notification::where('status', NotificationStatus::CANCELLED)->count(),
        ];

        $types = NotificationType::cases();
        $priorities = NotificationPriority::cases();
        $statuses = NotificationStatus::cases();
        $targetTypes = NotificationTargetType::cases();

        return view('super-admin.notifications.index', compact(
            'notifications',
            'stats',
            'types',
            'priorities',
            'statuses',
            'targetTypes',
            'search',
            'type',
            'priority',
            'status',
            'targetType',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create(Request $request): View
    {
        $stores = Store::where('status', StoreStatus::ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'city']);

        $types = NotificationType::cases();
        $priorities = NotificationPriority::cases();
        $targetTypes = NotificationTargetType::cases();
        $selectedStoreId = $request->get('store_id');

        return view('super-admin.notifications.create', compact(
            'stores',
            'types',
            'priorities',
            'targetTypes',
            'selectedStoreId'
        ));
    }

    /**
     * Store a newly created notification in database.
     */
    public function store(StoreNotificationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $deliveryMode = $validated['delivery_mode'];
        $status = NotificationStatus::DRAFT;
        $sentAt = null;
        $scheduledAt = null;

        if ($deliveryMode === 'now') {
            $status = NotificationStatus::SENT;
            $sentAt = Carbon::now();
        } elseif ($deliveryMode === 'schedule') {
            $status = NotificationStatus::SCHEDULED;
            $scheduledAt = Carbon::parse($validated['scheduled_at']);
        }

        $notification = Notification::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'target_type' => $validated['target_type'],
            'store_id' => $validated['target_type'] === NotificationTargetType::SPECIFIC_STORE->value ? $validated['store_id'] : null,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'sent_at' => $sentAt,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $statusMsg = match ($status) {
            NotificationStatus::SENT => 'Notification published and marked as sent.',
            NotificationStatus::SCHEDULED => "Notification scheduled for delivery at {$scheduledAt->format('M d, Y h:i A')}.",
            default => 'Notification saved as draft.',
        };

        return redirect()->route('super-admin.notifications.show', $notification)
            ->with('success', $statusMsg);
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification): View
    {
        $notification->load(['store.owners', 'creator', 'updater']);

        return view('super-admin.notifications.show', compact('notification'));
    }

    /**
     * Show the form for editing the notification.
     */
    public function edit(Notification $notification): View|RedirectResponse
    {
        if (! $notification->canBeEdited()) {
            return redirect()->route('super-admin.notifications.show', $notification)
                ->with('error', 'Sent notifications cannot be edited to preserve delivery history and audit logs.');
        }

        $stores = Store::where('status', StoreStatus::ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'city']);

        $types = NotificationType::cases();
        $priorities = NotificationPriority::cases();
        $targetTypes = NotificationTargetType::cases();

        return view('super-admin.notifications.edit', compact(
            'notification',
            'stores',
            'types',
            'priorities',
            'targetTypes'
        ));
    }

    /**
     * Update the specified notification in database.
     */
    public function update(UpdateNotificationRequest $request, Notification $notification): RedirectResponse
    {
        if (! $notification->canBeEdited()) {
            return redirect()->route('super-admin.notifications.show', $notification)
                ->with('error', 'Sent notifications cannot be modified.');
        }

        $validated = $request->validated();

        $deliveryMode = $validated['delivery_mode'];
        $status = NotificationStatus::DRAFT;
        $sentAt = null;
        $scheduledAt = null;

        if ($deliveryMode === 'now') {
            $status = NotificationStatus::SENT;
            $sentAt = Carbon::now();
        } elseif ($deliveryMode === 'schedule') {
            $status = NotificationStatus::SCHEDULED;
            $scheduledAt = Carbon::parse($validated['scheduled_at']);
        }

        $notification->update([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'target_type' => $validated['target_type'],
            'store_id' => $validated['target_type'] === NotificationTargetType::SPECIFIC_STORE->value ? $validated['store_id'] : null,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'sent_at' => $sentAt,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('super-admin.notifications.show', $notification)
            ->with('success', 'Notification updated successfully.');
    }

    /**
     * Cancel a scheduled notification.
     */
    public function cancel(Notification $notification): RedirectResponse
    {
        if (! $notification->isScheduled()) {
            return redirect()->back()
                ->with('error', 'Only scheduled notifications can be cancelled.');
        }

        $notification->update([
            'status' => NotificationStatus::CANCELLED,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Scheduled notification cancelled successfully.');
    }

    /**
     * Remove the specified notification from database.
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        if ($notification->isSent()) {
            return redirect()->back()
                ->with('error', 'Sent notifications cannot be deleted to maintain audit integrity.');
        }

        $notification->delete();

        return redirect()->route('super-admin.notifications.index')
            ->with('success', 'Notification removed successfully.');
    }
}
