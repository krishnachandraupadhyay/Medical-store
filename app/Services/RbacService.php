<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RbacService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Dictionary of all granular system permissions grouped by module.
     *
     * @return array<string, array<string, string>>
     */
    public static function permissionDictionary(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => 'View Store Dashboard',
            ],
            'Customers' => [
                'customers.view' => 'View Customer Directory & History',
                'customers.create' => 'Create New Customers',
                'customers.edit' => 'Edit Customer Details',
                'customers.delete' => 'Delete Customers',
            ],
            'Suppliers' => [
                'suppliers.view' => 'View Supplier Directory & Details',
                'suppliers.create' => 'Create New Suppliers',
                'suppliers.edit' => 'Edit Supplier Details',
                'suppliers.delete' => 'Delete Suppliers',
            ],
            'Medicines' => [
                'medicines.view' => 'View Medicine Catalog & Formulations',
                'medicines.create' => 'Add New Medicines',
                'medicines.edit' => 'Edit Medicine Information',
                'medicines.delete' => 'Delete Medicines',
            ],
            'Inventory' => [
                'inventory.view' => 'View Stock Levels & Batches',
                'inventory.adjust' => 'Perform Manual Stock Adjustments',
                'inventory.reconcile' => 'Conduct Stock Counts & Physical Reconciliation',
                'inventory.damage' => 'Record Damaged Stock Write-offs',
                'inventory.loss' => 'Record Lost/Missing Stock Write-offs',
                'inventory.expiry' => 'Dispose Expired Medicine Batches',
            ],
            'Sales' => [
                'sales.view' => 'View Invoices & Sales Orders',
                'sales.create' => 'Create Invoices & Access POS Terminal',
                'sales.edit' => 'Edit Draft Sales',
                'sales.complete' => 'Complete Sales & Dispatch Stock',
                'sales.cancel' => 'Cancel Completed or Draft Invoices',
                'sales.return' => 'Process Customer Medicine Returns',
            ],
            'Purchases' => [
                'purchases.view' => 'View Purchases & Supplier Orders',
                'purchases.create' => 'Create Purchase Orders & Stock In',
                'purchases.edit' => 'Edit Draft Purchase Orders',
                'purchases.complete' => 'Receive & Complete Purchases',
                'purchases.cancel' => 'Cancel Purchase Orders',
                'purchases.return' => 'Process Supplier Purchase Returns',
            ],
            'Payments' => [
                'customer_payments.view' => 'View Customer Payments & Receipts',
                'customer_payments.create' => 'Collect & Record Customer Payments',
                'supplier_payments.view' => 'View Supplier Payments & Disbursals',
                'supplier_payments.create' => 'Record Payments to Suppliers',
            ],
            'Expenses' => [
                'expenses.view' => 'View Store Operating Expenses',
                'expenses.create' => 'Record Operating Expenses',
                'expenses.edit' => 'Edit Recorded Expenses',
                'expenses.delete' => 'Delete Expense Records',
            ],
            'Reports' => [
                'reports.view' => 'View Operational & Business Reports',
                'reports.export' => 'Export Business Reports & Ledger Data',
            ],
            'Notifications' => [
                'notifications.view' => 'View Store Notifications & Alerts',
            ],
            'Staff' => [
                'staff.view' => 'View Staff Members',
                'staff.create' => 'Create New Staff Accounts',
                'staff.edit' => 'Edit Staff Profiles & Roles',
                'staff.deactivate' => 'Activate or Deactivate Staff Accounts',
                'staff.permissions' => 'Manage Custom Roles & Permissions',
            ],
            'Settings' => [
                'settings.view' => 'View Store Settings',
                'settings.edit' => 'Update Store Settings & Configuration',
            ],
        ];
    }

    /**
     * Dictionary of default system roles and their permission slugs.
     *
     * @return array<string, array{name: string, description: string, permissions: array<string>}>
     */
    public static function systemRoleDefinitions(): array
    {
        return [
            'store-manager' => [
                'name' => 'Store Manager',
                'description' => 'Full operational authority over store inventory, sales, purchases, and reporting, excluding subscription management.',
                'permissions' => [
                    'dashboard.view',
                    'customers.view', 'customers.create', 'customers.edit',
                    'suppliers.view', 'suppliers.create', 'suppliers.edit',
                    'medicines.view', 'medicines.create', 'medicines.edit',
                    'inventory.view', 'inventory.adjust', 'inventory.reconcile', 'inventory.damage', 'inventory.loss', 'inventory.expiry',
                    'sales.view', 'sales.create', 'sales.edit', 'sales.complete', 'sales.return',
                    'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.complete', 'purchases.return',
                    'customer_payments.view', 'customer_payments.create',
                    'supplier_payments.view', 'supplier_payments.create',
                    'expenses.view', 'expenses.create', 'expenses.edit',
                    'reports.view', 'reports.export',
                    'notifications.view',
                    'staff.view',
                    'settings.view',
                ],
            ],
            'sales-staff' => [
                'name' => 'Sales Staff',
                'description' => 'Authorized to process sales, create customers, use the POS terminal, and record customer payments.',
                'permissions' => [
                    'dashboard.view',
                    'customers.view', 'customers.create', 'customers.edit',
                    'medicines.view',
                    'inventory.view',
                    'sales.view', 'sales.create', 'sales.edit', 'sales.complete',
                    'customer_payments.view', 'customer_payments.create',
                    'notifications.view',
                ],
            ],
            'purchase-staff' => [
                'name' => 'Purchase Staff',
                'description' => 'Authorized to manage suppliers, create purchase orders, receive supplier stock, and process purchase returns.',
                'permissions' => [
                    'dashboard.view',
                    'suppliers.view', 'suppliers.create', 'suppliers.edit',
                    'medicines.view',
                    'inventory.view',
                    'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.complete', 'purchases.return',
                    'supplier_payments.view',
                    'notifications.view',
                ],
            ],
            'inventory-staff' => [
                'name' => 'Inventory Staff',
                'description' => 'Dedicated inventory manager for stock movements, physical audits, reconciliation, and damaged/expired write-offs.',
                'permissions' => [
                    'dashboard.view',
                    'medicines.view', 'medicines.create', 'medicines.edit',
                    'inventory.view', 'inventory.adjust', 'inventory.reconcile', 'inventory.damage', 'inventory.loss', 'inventory.expiry',
                    'notifications.view',
                ],
            ],
            'account-staff' => [
                'name' => 'Account Staff',
                'description' => 'Manages store operating expenses, customer/supplier payment reconciliations, and financial reports.',
                'permissions' => [
                    'dashboard.view',
                    'customer_payments.view', 'customer_payments.create',
                    'supplier_payments.view', 'supplier_payments.create',
                    'expenses.view', 'expenses.create', 'expenses.edit',
                    'reports.view', 'reports.export',
                    'notifications.view',
                ],
            ],
            'viewer' => [
                'name' => 'Viewer',
                'description' => 'Read-only access across all primary modules without modification or operational capabilities.',
                'permissions' => [
                    'dashboard.view',
                    'customers.view',
                    'suppliers.view',
                    'medicines.view',
                    'inventory.view',
                    'sales.view',
                    'purchases.view',
                    'customer_payments.view',
                    'supplier_payments.view',
                    'expenses.view',
                    'reports.view',
                    'notifications.view',
                ],
            ],
        ];
    }

    /**
     * Seed or sync all standard system permissions.
     */
    public function seedDefaultPermissions(): void
    {
        foreach (self::permissionDictionary() as $group => $perms) {
            foreach ($perms as $slug => $name) {
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'group' => $group,
                        'description' => $name,
                    ]
                );
            }
        }
    }

    /**
     * Seed or update system default roles (global templates with store_id = null).
     */
    public function seedDefaultSystemRoles(): void
    {
        $this->seedDefaultPermissions();

        $allPermissions = Permission::all()->keyBy('slug');

        foreach (self::systemRoleDefinitions() as $slug => $def) {
            $role = Role::firstOrCreate(
                ['store_id' => null, 'slug' => $slug],
                [
                    'name' => $def['name'],
                    'description' => $def['description'],
                    'is_system' => true,
                    'status' => 'active',
                ]
            );

            // Sync designated permissions
            $permissionIds = collect($def['permissions'])
                ->map(fn (string $permSlug) => $allPermissions->get($permSlug)?->id)
                ->filter()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }

    /**
     * Get all available roles for a given store (System roles + Store custom roles).
     */
    public function getAvailableRolesForStore(Store|int $store): Collection
    {
        $storeId = $store instanceof Store ? $store->id : $store;

        return Role::with('permissions')
            ->forStore($storeId)
            ->active()
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if a user possesses a specific permission.
     */
    public function userHasPermission(User $user, string $permissionSlug): bool
    {
        if ($user->isSuperAdmin() || $user->isStoreOwner()) {
            return true;
        }

        if (! $user->isActive() || ! $user->role_id) {
            return false;
        }

        $userPermissions = $this->getUserPermissionSlugs($user);

        return in_array($permissionSlug, $userPermissions, true);
    }

    /**
     * Get all permission slugs for a user, memoized with Cache.
     *
     * @return array<string>
     */
    public function getUserPermissionSlugs(User $user): array
    {
        $cacheKey = "user_permissions_{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            if (! $user->role_id) {
                return [];
            }

            $role = Role::with('permissions')->find($user->role_id);
            if (! $role || ! $role->isActive()) {
                return [];
            }

            return $role->permissions->pluck('slug')->all();
        });
    }

    /**
     * Invalidate cached permissions for a specific user.
     */
    public function clearUserPermissionCache(User|int $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        Cache::forget("user_permissions_{$userId}");
    }

    /**
     * Invalidate cached permissions for all users assigned to a specific role.
     */
    public function clearRolePermissionCache(Role|int $role): void
    {
        $roleId = $role instanceof Role ? $role->id : $role;
        $userIds = User::where('role_id', $roleId)->pluck('id');

        foreach ($userIds as $id) {
            Cache::forget("user_permissions_{$id}");
        }
    }

    /**
     * Verify if a role is safe to delete.
     */
    public function canDeleteRole(Role $role): bool
    {
        if ($role->isSystem()) {
            return false;
        }

        return ! User::where('role_id', $role->id)->where('is_active', true)->exists();
    }
}
