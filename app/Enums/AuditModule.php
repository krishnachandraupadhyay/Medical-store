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
    case INVENTORY = 'inventory';
    case PURCHASES = 'purchases';
    case SUPPLIERS = 'suppliers';
    case CUSTOMERS = 'customers';
    case SALES = 'sales';
    case SALES_RETURNS = 'sales_returns';
    case PURCHASE_RETURNS = 'purchase_returns';
    case EXPENSES = 'expenses';
    case STORE_PAYMENTS = 'store_payments';
    case STAFF = 'staff';
    case ROLES = 'roles';
    case CONTACT_NOTES = 'contact_notes';

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
            self::INVENTORY => 'Inventory',
            self::PURCHASES => 'Purchases',
            self::SUPPLIERS => 'Suppliers',
            self::CUSTOMERS => 'Customers',
            self::SALES => 'Sales',
            self::SALES_RETURNS => 'Sales Returns',
            self::PURCHASE_RETURNS => 'Purchase Returns',
            self::EXPENSES => 'Expenses',
            self::STORE_PAYMENTS => 'Store Payments',
            self::STAFF => 'Staff Management',
            self::ROLES => 'Role Management',
            self::CONTACT_NOTES => 'Contact Notes',
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
            self::INVENTORY => '📦',
            self::PURCHASES => '🛒',
            self::SUPPLIERS => '🚚',
            self::CUSTOMERS => '👥',
            self::SALES => '🧾',
            self::SALES_RETURNS => '↩️',
            self::PURCHASE_RETURNS => '↪️',
            self::EXPENSES => '💸',
            self::STORE_PAYMENTS => '💵',
            self::STAFF => '👨‍⚕️',
            self::ROLES => '🛡️',
            self::CONTACT_NOTES => '📝',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::AUTH => 'bg-purple-50 text-purple-700 border-purple-200',
            self::STORES, self::PURCHASES => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::STORE_OWNERS, self::STAFF => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::SUBSCRIPTION_PLANS => 'bg-sky-50 text-sky-700 border-sky-200',
            self::STORE_SUBSCRIPTIONS => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            self::PAYMENTS, self::STORE_PAYMENTS => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::NOTIFICATIONS, self::SUPPLIERS => 'bg-amber-50 text-amber-700 border-amber-200',
            self::SETTINGS, self::ROLES => 'bg-slate-100 text-slate-700 border-slate-200',
            self::INVENTORY => 'bg-teal-50 text-teal-700 border-teal-200',
            self::CUSTOMERS => 'bg-violet-50 text-violet-700 border-violet-200',
            self::SALES => 'bg-blue-50 text-blue-700 border-blue-200',
            self::SALES_RETURNS, self::PURCHASE_RETURNS => 'bg-orange-50 text-orange-700 border-orange-200',
            self::EXPENSES => 'bg-rose-50 text-rose-700 border-rose-200',
            self::CONTACT_NOTES => 'bg-lime-50 text-lime-700 border-lime-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
