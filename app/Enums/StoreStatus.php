<?php

namespace App\Enums;

enum StoreStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case SUSPENDED = 'SUSPENDED';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }

    /**
     * Get styling badge color classes.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::INACTIVE => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
            self::SUSPENDED => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
        };
    }
}
