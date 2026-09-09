<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case NORMAL = 'normal';
    case IMPORTANT = 'important';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::IMPORTANT => 'Important',
            self::URGENT => 'Urgent',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NORMAL => 'bg-slate-100 text-slate-700 border-slate-200',
            self::IMPORTANT => 'bg-amber-50 text-amber-700 border-amber-200',
            self::URGENT => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
