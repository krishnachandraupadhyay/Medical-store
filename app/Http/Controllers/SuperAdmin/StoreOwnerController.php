<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreOwner\StoreStoreOwnerRequest;
use App\Http\Requests\SuperAdmin\StoreOwner\UpdateStoreOwnerRequest;
use App\Models\Store;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StoreOwnerController extends Controller
{
    /**
     * Display a paginated listing of all Store Owners.
     */
    public function index(Request $request): View
    {
        $query = User::storeOwners()->with('store');

        // 1. Search filter
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($storeQuery) use ($search) {
                        $storeQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status filter (active, inactive)
        $status = $request->input('status');
        if ($status !== null && $status !== '') {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // 3. Store filter
        $storeId = $request->input('store_id');
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        // Counts for status tabs
        $counts = [
            'all' => User::storeOwners()->count(),
            'active' => User::storeOwners()->where('is_active', true)->count(),
            'inactive' => User::storeOwners()->where('is_active', false)->count(),
        ];

        $storeOwners = $query->latest()->paginate(10)->withQueryString();
        $stores = Store::orderBy('name')->get(['id', 'name', 'code']);

        return view('super-admin.store-owners.index', [
            'storeOwners' => $storeOwners,
            'stores' => $stores,
            'counts' => $counts,
            'currentStatus' => $status,
            'selectedStoreId' => $storeId,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new Store Owner.
     */
    public function create(): View
    {
        $stores = Store::orderBy('name')->get(['id', 'name', 'code', 'status']);

        return view('super-admin.store-owners.create', [
            'stores' => $stores,
        ]);
    }

    /**
     * Store a newly created Store Owner in storage.
     */
    public function store(StoreStoreOwnerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $owner = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::STORE_OWNER,
            'store_id' => $data['store_id'],
            'is_active' => (bool) $data['is_active'],
            'email_verified_at' => now(),
        ]);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::STORE_OWNERS,
            "Created store owner: {$owner->name} ({$owner->email})",
            $owner,
            null,
            $owner->toArray()
        );

        return redirect()->route('super-admin.store-owners.show', $owner)
            ->with('status', "Store Owner [{$owner->name}] created and assigned successfully!");
    }

    /**
     * Display the specified Store Owner details.
     */
    public function show(User $user): View
    {
        abort_unless($user->isStoreOwner(), 404);

        $user->load('store');

        return view('super-admin.store-owners.show', [
            'owner' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified Store Owner.
     */
    public function edit(User $user): View
    {
        abort_unless($user->isStoreOwner(), 404);

        $stores = Store::orderBy('name')->get(['id', 'name', 'code', 'status']);

        return view('super-admin.store-owners.edit', [
            'owner' => $user,
            'stores' => $stores,
        ]);
    }

    /**
     * Update the specified Store Owner in storage.
     */
    public function update(UpdateStoreOwnerRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->isStoreOwner(), 404);

        $data = $request->validated();
        $oldValues = $user->toArray();

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'store_id' => $data['store_id'],
            'is_active' => (bool) $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::STORE_OWNERS,
            "Updated store owner profile: {$user->name}",
            $user,
            $oldValues,
            $user->toArray()
        );

        return redirect()->route('super-admin.store-owners.show', $user)
            ->with('status', "Store Owner [{$user->name}] profile updated successfully!");
    }

    /**
     * Toggle the active/inactive status of a Store Owner.
     */
    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isStoreOwner(), 404);

        $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $newActiveState = (bool) $request->input('is_active');
        $oldState = (bool) $user->is_active;

        // If activating, verify no other active primary owner exists on that store
        if ($newActiveState && $user->store_id) {
            $existingActiveOwner = User::where('store_id', $user->store_id)
                ->where('role', UserRole::STORE_OWNER)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingActiveOwner) {
                return redirect()->back()->with('error', "Cannot activate this owner: {$existingActiveOwner->name} is currently the active primary owner of {$user->store->name}.");
            }
        }

        $user->update(['is_active' => $newActiveState]);

        AuditLogger::log(
            $newActiveState ? AuditAction::ACTIVATED : AuditAction::DEACTIVATED,
            AuditModule::STORE_OWNERS,
            "Updated store owner [{$user->name}] status to ".($newActiveState ? 'Active' : 'Inactive').'.',
            $user,
            ['is_active' => $oldState],
            ['is_active' => $newActiveState]
        );

        $stateText = $newActiveState ? 'Activated' : 'Deactivated';

        return redirect()->back()
            ->with('status', "Store Owner [{$user->name}] has been {$stateText} successfully.");
    }
}
