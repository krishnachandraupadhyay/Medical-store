<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case STORE_OWNER = 'STORE_OWNER';
    case STORE_STAFF = 'STORE_STAFF';

    /**
     * Get a human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::STORE_OWNER => 'Store Owner',
            self::STORE_STAFF => 'Store Staff',
        };
    }
}
