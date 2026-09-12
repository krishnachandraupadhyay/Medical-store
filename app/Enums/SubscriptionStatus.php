<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case TRIAL = 'trial';
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case SUSPENDED = 'suspended';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::TRIAL => 'Trial Period',
            self::ACTIVE => 'Active',
            self::PENDING => 'Pending',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
            self::SUSPENDED => 'Suspended',
        };
    }

    /**
     * Get badge styling classes (Blue & White theme compliant).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::TRIAL => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
            self::EXPIRED => 'bg-slate-100 text-slate-700 border-slate-300',
            self::CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::SUSPENDED => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    }

    /**
     * Check if currently active or trial.
     */
    public function isOperable(): bool
    {
        return in_array($this, [self::ACTIVE, self::TRIAL], true);
    }

    /**
     * Check if status is active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if status is trial.
     */
    public function isTrial(): bool
    {
        return $this === self::TRIAL;
    }

    /**
     * Check if status is pending.
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if status is expired.
     */
    public function isExpired(): bool
    {
        return $this === self::EXPIRED;
    }

    /**
     * Check if status is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    /**
     * Check if status is suspended.
     */
    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }
}
