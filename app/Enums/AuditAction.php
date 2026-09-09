<?php

namespace App\Enums;

enum AuditAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case ACTIVATED = 'activated';
    case DEACTIVATED = 'deactivated';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case STATUS_CHANGED = 'status_changed';
    case SETTINGS_UPDATED = 'settings_updated';
    case ASSIGNED = 'assigned';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::UPDATED => 'Updated',
            self::DELETED => 'Deleted',
            self::ACTIVATED => 'Activated',
            self::DEACTIVATED => 'Deactivated',
            self::SUSPENDED => 'Suspended',
            self::CANCELLED => 'Cancelled',
            self::LOGIN => 'Logged In',
            self::LOGOUT => 'Logged Out',
            self::STATUS_CHANGED => 'Status Changed',
            self::SETTINGS_UPDATED => 'Settings Updated',
            self::ASSIGNED => 'Assigned',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::CREATED, self::ACTIVATED, self::ASSIGNED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::UPDATED, self::SETTINGS_UPDATED, self::STATUS_CHANGED => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::LOGIN, self::LOGOUT => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::SUSPENDED, self::CANCELLED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::DEACTIVATED, self::DELETED => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
