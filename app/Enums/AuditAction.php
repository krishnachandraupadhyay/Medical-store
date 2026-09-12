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
    case ACCESS_DENIED = 'access_denied';
    case EXPIRED = 'expired';
    case COMPLETED = 'completed';
    case PASSWORD_RESET = 'password_reset';
    case PERMISSIONS_CHANGED = 'permissions_changed';
    case NOTE_ADDED = 'note_added';
    case NOTE_DELETED = 'note_deleted';
    case FOLLOWUP_COMPLETED = 'followup_completed';

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
            self::ACCESS_DENIED => 'Access Denied',
            self::EXPIRED => 'Expired',
            self::COMPLETED => 'Completed',
            self::PASSWORD_RESET => 'Password Reset',
            self::PERMISSIONS_CHANGED => 'Permissions Changed',
            self::NOTE_ADDED => 'Note Added',
            self::NOTE_DELETED => 'Note Deleted',
            self::FOLLOWUP_COMPLETED => 'Follow-up Completed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::CREATED, self::ACTIVATED, self::ASSIGNED, self::COMPLETED, self::NOTE_ADDED, self::FOLLOWUP_COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::UPDATED, self::SETTINGS_UPDATED, self::STATUS_CHANGED, self::PERMISSIONS_CHANGED => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::LOGIN, self::LOGOUT, self::PASSWORD_RESET => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::SUSPENDED, self::CANCELLED, self::EXPIRED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::DEACTIVATED, self::DELETED, self::ACCESS_DENIED, self::NOTE_DELETED => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
