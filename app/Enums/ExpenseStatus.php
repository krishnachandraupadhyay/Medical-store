<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case DRAFT = 'draft';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-amber-50 text-amber-700 border-amber-200',
            self::PAID => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::CANCELLED => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }
}
