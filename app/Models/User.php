<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Services\RbacService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'mobile', 'password', 'role', 'role_id', 'is_active', 'store_id', 'last_login_at', 'created_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'role_id' => 'integer',
            'is_active' => 'boolean',
            'store_id' => 'integer',
            'created_by' => 'integer',
        ];
    }

    /**
     * The medical store this user belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Staff role assigned to this user.
     */
    public function staffRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * User who created this account.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'user_id');
    }

    /**
     * Scope a query to only include store owners.
     */
    public function scopeStoreOwners(Builder $query): Builder
    {
        return $query->where('role', UserRole::STORE_OWNER);
    }

    /**
     * Check if user is an active Super Administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN && (bool) $this->is_active;
    }

    /**
     * Check if user is a Store Owner.
     */
    public function isStoreOwner(): bool
    {
        return $this->role === UserRole::STORE_OWNER;
    }

    /**
     * Check if user is Store Staff.
     */
    public function isStaff(): bool
    {
        return $this->role === UserRole::STORE_STAFF;
    }

    /**
     * Check if account is active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Check if user has a specific permission.
     * Store Owner has all store permissions by default.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin() || $this->isStoreOwner()) {
            return true;
        }

        if (! $this->isActive() || ! $this->role_id) {
            return false;
        }

        return app(RbacService::class)->userHasPermission($this, $permissionSlug);
    }
}
