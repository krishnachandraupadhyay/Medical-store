<?php

namespace App\Enums;

enum BatchStatus: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case EXPIRED = 'expired';
    case DEPLETED = 'depleted';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::BLOCKED => 'Blocked',
            self::EXPIRED => 'Expired',
            self::DEPLETED => 'Depleted (0 Qty)',
            self::INACTIVE => 'Inactive',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::BLOCKED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::EXPIRED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::DEPLETED, self::INACTIVE => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function canBeSold(): bool
    {
        return $this === self::ACTIVE;
    }
}
