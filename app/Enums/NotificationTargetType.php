<?php

namespace App\Enums;

enum NotificationTargetType: string
{
    case ALL_STORES = 'all_stores';
    case SPECIFIC_STORE = 'specific_store';

    public function label(): string
    {
        return match ($this) {
            self::ALL_STORES => 'All Stores (Platform-wide)',
            self::SPECIFIC_STORE => 'Specific Store',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
