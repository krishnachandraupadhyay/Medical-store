<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    // Backwards compatibility aliases
    case IMPORTANT = 'important';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::NORMAL, self::MEDIUM => 'Normal',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
            self::IMPORTANT => 'Important',
            self::URGENT => 'Urgent',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::LOW => 'bg-slate-100 text-slate-600 border-slate-200',
            self::NORMAL, self::MEDIUM => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::HIGH, self::IMPORTANT => 'bg-amber-50 text-amber-700 border-amber-200',
            self::CRITICAL, self::URGENT => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
