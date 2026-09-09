<?php

namespace App\Enums;

enum AuditModule: string
{
    case AUTH = 'auth';
    case STORES = 'stores';
    case STORE_OWNERS = 'store_owners';
    case SUBSCRIPTION_PLANS = 'subscription_plans';
    case STORE_SUBSCRIPTIONS = 'store_subscriptions';
    case PAYMENTS = 'payments';
    case NOTIFICATIONS = 'notifications';
    case SETTINGS = 'settings';

    public function label(): string
    {
        return match ($this) {
            self::AUTH => 'Authentication',
            self::STORES => 'Stores',
            self::STORE_OWNERS => 'Store Owners',
            self::SUBSCRIPTION_PLANS => 'Subscription Plans',
            self::STORE_SUBSCRIPTIONS => 'Store Subscriptions',
            self::PAYMENTS => 'Payments',
            self::NOTIFICATIONS => 'Notifications',
            self::SETTINGS => 'System Settings',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AUTH => '🔐',
            self::STORES => '🏥',
            self::STORE_OWNERS => '👤',
            self::SUBSCRIPTION_PLANS => '📦',
            self::STORE_SUBSCRIPTIONS => '📋',
            self::PAYMENTS => '💳',
            self::NOTIFICATIONS => '🔔',
            self::SETTINGS => '⚙️',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::AUTH => 'bg-purple-50 text-purple-700 border-purple-200',
            self::STORES => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::STORE_OWNERS => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::SUBSCRIPTION_PLANS => 'bg-sky-50 text-sky-700 border-sky-200',
            self::STORE_SUBSCRIPTIONS => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            self::PAYMENTS => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::NOTIFICATIONS => 'bg-amber-50 text-amber-700 border-amber-200',
            self::SETTINGS => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
