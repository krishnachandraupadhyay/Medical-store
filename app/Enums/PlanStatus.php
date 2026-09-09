<?php

namespace App\Enums;

enum PlanStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    /**
     * Get badge styling classes.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::INACTIVE => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    /**
     * Check if active.
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
