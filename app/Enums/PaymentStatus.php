<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case NOT_APPLICABLE = 'not_applicable';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::UNPAID => 'Unpaid',
            self::PARTIAL => 'Partial',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::NOT_APPLICABLE => 'N/A',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING, self::UNPAID => 'bg-amber-50 text-amber-700 border-amber-200',
            self::PARTIAL => 'bg-blue-50 text-blue-700 border-blue-200',
            self::PAID => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::FAILED, self::CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::REFUNDED, self::NOT_APPLICABLE => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
