<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Staff\StoreStaffRequest;
use App\Http\Requests\Store\Staff\UpdateStaffRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use App\Services\RbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(
        protected RbacService $rbacService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of staff members for the current store.
     */
    public function index(Request $request): View
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        $query = User::with('staffRole')
            ->where('store_id', $store->id)
            ->where('role', UserRole::STORE_STAFF);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        if (in_array($sortBy, ['name', 'email', 'created_at', 'last_login_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $staffMembers = $query->paginate(15)->withQueryString();
        $availableRoles = $this->rbacService->getAvailableRolesForStore($store);

        $quotaRemaining = subscription_access()->remaining($store, 'max_staff');
        $quotaLimit = subscription_access()->getLimit($store, 'max_staff');
        $isQuotaUnlimited = subscription_access()->isUnlimited($store, 'max_staff');

        return view('store.staff.index', compact(
            'staffMembers',
            'availableRoles',
            'quotaRemaining',
            'quotaLimit',
            'isQuotaUnlimited'
        ));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create(): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        // Check subscription quota limit
        if (! subscription_access()->checkLimit($store, 'max_staff')) {
            return redirect()->route('store.staff.index')
                ->with('error', 'Staff member limit reached for your active subscription plan. Please upgrade to add more staff.');
        }

        $availableRoles = $this->rbacService->getAvailableRolesForStore($store);

        return view('store.staff.create', compact('availableRoles'));
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        // Verify quota limit
        if (! subscription_access()->checkLimit($store, 'max_staff')) {
            return back()
                ->withInput()
                ->withErrors(['quota' => 'Staff member limit reached for your active subscription plan.']);
        }

        $validated = $request->validated();

        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => UserRole::STORE_STAFF,
            'role_id' => $validated['role_id'],
            'store_id' => $store->id,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        $staff->load('staffRole');

        // Audit Log
        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::STAFF,
            "Created new staff account '{$staff->name}' ({$staff->email}) with role '{$staff->staffRole?->name}'.",
            $staff,
            null,
            $staff->only(['name', 'email', 'mobile', 'role_id', 'is_active']),
            Auth::user()
        );

        // Notify Store Owner
        $this->notificationService->notify([
            'store_id' => $store->id,
            'type' => NotificationType::STAFF_CREATED,
            'title' => 'New Staff Account Created',
            'message' => "Staff member '{$staff->name}' was added with role '{$staff->staffRole?->name}'.",
            'priority' => NotificationPriority::NORMAL,
            'action_url' => route('store.staff.show', $staff),
            'reference_type' => User::class,
            'reference_id' => $staff->id,
        ]);

        return redirect()->route('store.staff.index')
            ->with('success', "Staff member '{$staff->name}' successfully created.");
    }

    /**
     * Display the specified staff member details and audit activity.
     */
    public function show(User $staff): View
    {
        $store = current_store();
        abort_unless($store && $staff->store_id === $store->id && $staff->isStaff(), 404, 'Staff member not found.');

        $staff->load(['staffRole.permissions', 'creator']);

        $recentActivities = AuditLog::where(function ($q) use ($staff) {
            $q->where('user_id', $staff->id)
                ->orWhere(function ($sub) use ($staff) {
                    $sub->where('subject_type', User::class)
                        ->where('subject_id', $staff->id);
                });
        })
            ->latest('id')
            ->take(20)
            ->get();

        return view('store.staff.show', compact('staff', 'recentActivities'));
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(User $staff): View
    {
        $store = current_store();
        abort_unless($store && $staff->store_id === $store->id && $staff->isStaff(), 404, 'Staff member not found.');

        $availableRoles = $this->rbacService->getAvailableRolesForStore($store);

        return view('store.staff.edit', compact('staff', 'availableRoles'));
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $staff->store_id === $store->id && $staff->isStaff(), 404, 'Staff member not found.');

        $validated = $request->validated();

        $oldValues = $staff->only(['name', 'email', 'mobile', 'role_id', 'is_active']);
        $roleChanged = (int) $staff->role_id !== (int) $validated['role_id'];

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->mobile = $validated['mobile'] ?? null;
        $staff->role_id = $validated['role_id'];

        if ($request->has('is_active')) {
            $staff->is_active = $request->boolean('is_active');
        }

        if (! empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }

        $staff->save();
        $staff->load('staffRole');

        // Clear permission cache
        $this->rbacService->clearUserPermissionCache($staff);

        // Audit Log
        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STAFF,
            "Updated staff profile for '{$staff->name}'.",
            $staff,
            $oldValues,
            $staff->only(['name', 'email', 'mobile', 'role_id', 'is_active']),
            Auth::user()
        );

        if ($roleChanged) {
            $this->notificationService->notify([
                'store_id' => $store->id,
                'type' => NotificationType::STAFF_ROLE_CHANGED,
                'title' => 'Staff Role Updated',
                'message' => "Role for '{$staff->name}' changed to '{$staff->staffRole?->name}'.",
                'priority' => NotificationPriority::NORMAL,
                'action_url' => route('store.staff.show', $staff),
                'reference_type' => User::class,
                'reference_id' => $staff->id,
            ]);
        }

        return redirect()->route('store.staff.show', $staff)
            ->with('success', "Staff member '{$staff->name}' successfully updated.");
    }

    /**
     * Toggle the active status of a staff member.
     */
    public function toggleStatus(Request $request, User $staff): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $staff->store_id === $store->id && $staff->isStaff(), 404, 'Staff member not found.');

        $oldStatus = $staff->is_active;
        $newStatus = ! $oldStatus;

        // If activating, check subscription quota
        if ($newStatus && ! subscription_access()->checkLimit($store, 'max_staff')) {
            return back()->with('error', 'Cannot activate staff member. Active staff quota limit reached.');
        }

        $staff->is_active = $newStatus;
        $staff->save();

        // Invalidate permission cache
        $this->rbacService->clearUserPermissionCache($staff);

        $action = $newStatus ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED;
        $statusText = $newStatus ? 'activated' : 'deactivated';

        AuditLogger::log(
            $action,
            AuditModule::STAFF,
            "Staff account '{$staff->name}' ({$staff->email}) was {$statusText}.",
            $staff,
            ['is_active' => $oldStatus],
            ['is_active' => $newStatus],
            Auth::user()
        );

        if (! $newStatus) {
            $this->notificationService->notify([
                'store_id' => $store->id,
                'type' => NotificationType::STAFF_DEACTIVATED,
                'title' => 'Staff Account Deactivated',
                'message' => "Staff account for '{$staff->name}' has been deactivated.",
                'priority' => NotificationPriority::HIGH,
                'action_url' => route('store.staff.show', $staff),
                'reference_type' => User::class,
                'reference_id' => $staff->id,
            ]);
        }

        return back()->with('success', "Staff account '{$staff->name}' successfully {$statusText}.");
    }

    /**
     * Securely reset password for a staff member.
     */
    public function resetPassword(Request $request, User $staff): RedirectResponse
    {
        $store = current_store();
        abort_unless($store && $staff->store_id === $store->id && $staff->isStaff(), 404, 'Staff member not found.');

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $staff->password = Hash::make($validated['password']);
        $staff->save();

        AuditLogger::log(
            AuditAction::PASSWORD_RESET,
            AuditModule::STAFF,
            "Password for staff member '{$staff->name}' was reset by store owner.",
            $staff,
            null,
            null,
            Auth::user()
        );

        return back()->with('success', "Password for '{$staff->name}' has been reset successfully.");
    }
}
