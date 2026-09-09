<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case SENT = 'sent';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::SENT => 'Sent',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-700 border-slate-200',
            self::SCHEDULED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::SENT => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::FAILED => 'bg-red-50 text-red-700 border-red-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
