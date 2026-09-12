<?php

namespace App\Http\Controllers\Store;

use App\Enums\AuditAction;
use App\Enums\AuditModule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\Role\StoreRoleRequest;
use App\Http\Requests\Store\Role\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use App\Services\RbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(
        protected RbacService $rbacService
    ) {}

    /**
     * Display a listing of system and custom roles for the current store.
     */
    public function index(): View
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        $roles = Role::with(['permissions'])
            ->withCount(['users' => function ($q) use ($store) {
                $q->where('store_id', $store->id)->where('is_active', true);
            }])
            ->forStore($store->id)
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return view('store.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new custom role.
     */
    public function create(): View
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        $permissionsByGroup = Permission::all()->groupBy('group');

        return view('store.roles.create', compact('permissionsByGroup'));
    }

    /**
     * Store a newly created custom role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        $validated = $request->validated();
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;

        // Ensure unique slug per store
        while (Role::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $role = Role::create([
            'store_id' => $store->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'status' => 'active',
        ]);

        $permissionIds = Permission::whereIn('slug', $validated['permissions'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        AuditLogger::log(
            AuditAction::CREATED,
            AuditModule::ROLES,
            "Created custom role '{$role->name}' with ".count($permissionIds).' permissions.',
            $role,
            null,
            ['name' => $role->name, 'slug' => $role->slug, 'permissions_count' => count($permissionIds)],
            Auth::user()
        );

        return redirect()->route('store.roles.index')
            ->with('success', "Custom role '{$role->name}' created successfully.");
    }

    /**
     * Show the form for editing the custom role.
     */
    public function edit(Role $role): View|RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        // Prevent cross-tenant editing
        if ($role->store_id !== null && $role->store_id !== $store->id) {
            abort(404, 'Role not found.');
        }

        if ($role->isSystem()) {
            return redirect()->route('store.roles.index')
                ->with('error', 'System default roles are protected and cannot be modified.');
        }

        $permissionsByGroup = Permission::all()->groupBy('group');
        $assignedPermissionSlugs = $role->permissions->pluck('slug')->all();

        return view('store.roles.edit', compact('role', 'permissionsByGroup', 'assignedPermissionSlugs'));
    }

    /**
     * Update the specified custom role.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        if ($role->store_id !== $store->id || $role->isSystem()) {
            abort(403, 'System or external roles cannot be modified.');
        }

        $validated = $request->validated();

        $oldValues = [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('slug')->all(),
        ];

        $role->name = $validated['name'];
        $role->description = $validated['description'] ?? null;
        $role->save();

        $permissionIds = Permission::whereIn('slug', $validated['permissions'])->pluck('id');
        $role->permissions()->sync($permissionIds);

        // Invalidate all assigned users' permission cache
        $this->rbacService->clearRolePermissionCache($role);

        AuditLogger::log(
            AuditAction::UPDATED,
            AuditModule::ROLES,
            "Updated custom role '{$role->name}' permissions.",
            $role,
            $oldValues,
            ['name' => $role->name, 'description' => $role->description, 'permissions_count' => count($permissionIds)],
            Auth::user()
        );

        return redirect()->route('store.roles.index')
            ->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Remove the specified custom role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $store = current_store();
        abort_unless($store, 404, 'Store not found.');

        if ($role->store_id !== $store->id || $role->isSystem()) {
            return back()->with('error', 'System default roles cannot be deleted.');
        }

        if (! $this->rbacService->canDeleteRole($role)) {
            return back()->with('error', 'Cannot delete role. Active staff members are currently assigned to this role. Please reassign them first.');
        }

        $roleName = $role->name;
        $role->permissions()->detach();
        $role->delete();

        AuditLogger::log(
            AuditAction::DELETED,
            AuditModule::ROLES,
            "Deleted custom role '{$roleName}'.",
            null,
            ['name' => $roleName],
            null,
            Auth::user()
        );

        return redirect()->route('store.roles.index')
            ->with('success', "Role '{$roleName}' deleted successfully.");
    }
}
