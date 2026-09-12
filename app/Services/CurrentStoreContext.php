<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CurrentStoreContext
{
    /**
     * Memoized current store instance.
     */
    protected ?Store $store = null;

    /**
     * Whether the store has been resolved in this lifecycle.
     */
    protected bool $resolved = false;

    /**
     * ID of the user for which the store was resolved.
     */
    protected ?int $resolvedUserId = null;

    /**
     * Resolve the current store for the authenticated context.
     * Guaranteed tenant-safe: resolves strictly through the authenticated User's store relationship.
     */
    public function get(): ?Store
    {
        $user = Auth::user();
        $currentUserId = $user?->id;

        if ($this->resolved && $this->resolvedUserId === $currentUserId) {
            return $this->store;
        }

        if ($user instanceof User && ($user->role === UserRole::STORE_OWNER || $user->role === UserRole::STORE_STAFF)) {
            $this->store = $user->store;
        } else {
            $this->store = null;
        }

        $this->resolved = true;
        $this->resolvedUserId = $currentUserId;

        return $this->store;
    }

    /**
     * Get the ID of the current store.
     */
    public function id(): ?int
    {
        return $this->get()?->id;
    }

    /**
     * Check if a valid store is currently resolved.
     */
    public function hasStore(): bool
    {
        return $this->get() !== null;
    }

    /**
     * Explicitly set the current store (used for testing or explicit Super Admin context).
     */
    public function set(?Store $store): void
    {
        $this->store = $store;
        $this->resolved = true;
        $this->resolvedUserId = Auth::id();
    }

    /**
     * Reset the memoized store.
     */
    public function reset(): void
    {
        $this->store = null;
        $this->resolved = false;
        $this->resolvedUserId = null;
    }
}
