<?php

namespace App\Enums;

enum NotificationType: string
{
    case GENERAL = 'general';
    case ANNOUNCEMENT = 'announcement';
    case SUBSCRIPTION = 'subscription';
    case PAYMENT = 'payment';
    case SYSTEM = 'system';
    case MAINTENANCE = 'maintenance';
    case WARNING = 'warning';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => 'General',
            self::ANNOUNCEMENT => 'Announcement',
            self::SUBSCRIPTION => 'Subscription Alert',
            self::PAYMENT => 'Payment Update',
            self::SYSTEM => 'System Notice',
            self::MAINTENANCE => 'Scheduled Maintenance',
            self::WARNING => 'Security Warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::GENERAL => '🔔',
            self::ANNOUNCEMENT => '📢',
            self::SUBSCRIPTION => '⭐',
            self::PAYMENT => '💳',
            self::SYSTEM => '⚙️',
            self::MAINTENANCE => '🛠️',
            self::WARNING => '⚠️',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::GENERAL => 'bg-slate-100 text-slate-700 border-slate-200',
            self::ANNOUNCEMENT => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::SUBSCRIPTION => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::PAYMENT => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::SYSTEM => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            self::MAINTENANCE => 'bg-amber-50 text-amber-700 border-amber-200',
            self::WARNING => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
