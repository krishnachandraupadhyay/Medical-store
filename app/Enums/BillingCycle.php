<?php

namespace App\Enums;

enum BillingCycle: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }

    /**
     * Get badge CSS classes.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::MONTHLY => 'bg-blue-50 text-blue-700 border-blue-200',
            self::YEARLY => 'bg-purple-50 text-purple-700 border-purple-200',
        };
    }
}
