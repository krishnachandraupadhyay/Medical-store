<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
            self::PAID => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::FAILED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::CANCELLED => 'bg-slate-100 text-slate-700 border-slate-200',
            self::REFUNDED => 'bg-purple-50 text-purple-700 border-purple-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
