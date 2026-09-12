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

    // Phase 23 Operational & Business Alert Types
    case LOW_STOCK = 'low_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case EXPIRING_BATCH = 'expiring_batch';
    case EXPIRED_BATCH = 'expired_batch';
    case CUSTOMER_OUTSTANDING = 'customer_outstanding';
    case SUPPLIER_OUTSTANDING = 'supplier_outstanding';
    case CUSTOMER_PAYMENT = 'customer_payment';
    case SUPPLIER_PAYMENT = 'supplier_payment';
    case SALE_COMPLETED = 'sale_completed';
    case PURCHASE_COMPLETED = 'purchase_completed';
    case SALES_RETURN_COMPLETED = 'sales_return_completed';
    case PURCHASE_RETURN_COMPLETED = 'purchase_return_completed';
    case EXPENSE_CREATED = 'expense_created';
    case SUBSCRIPTION_EXPIRING = 'subscription_expiring';
    case SUBSCRIPTION_EXPIRED = 'subscription_expired';

    // Phase 24 Advanced Inventory & Stock Control Types
    case STOCK_RECONCILIATION_COMPLETED = 'stock_reconciliation_completed';
    case DAMAGED_STOCK_RECORDED = 'damaged_stock_recorded';
    case LOST_STOCK_RECORDED = 'lost_stock_recorded';
    case EXPIRED_STOCK_PROCESSED = 'expired_stock_processed';
    case BATCH_BLOCKED = 'batch_blocked';

    // Phase 26 Staff Management & RBAC
    case STAFF_CREATED = 'staff_created';
    case STAFF_DEACTIVATED = 'staff_deactivated';
    case STAFF_ROLE_CHANGED = 'staff_role_changed';

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

            self::LOW_STOCK => 'Low Stock Alert',
            self::OUT_OF_STOCK => 'Out of Stock Alert',
            self::EXPIRING_BATCH => 'Expiring Batch Alert',
            self::EXPIRED_BATCH => 'Expired Stock Alert',
            self::CUSTOMER_OUTSTANDING => 'Customer Due Reminder',
            self::SUPPLIER_OUTSTANDING => 'Supplier Due Reminder',
            self::CUSTOMER_PAYMENT => 'Customer Payment Received',
            self::SUPPLIER_PAYMENT => 'Supplier Payment Recorded',
            self::SALE_COMPLETED => 'Sale Completed',
            self::PURCHASE_COMPLETED => 'Purchase Completed',
            self::SALES_RETURN_COMPLETED => 'Sales Return Completed',
            self::PURCHASE_RETURN_COMPLETED => 'Purchase Return Completed',
            self::EXPENSE_CREATED => 'Expense Recorded',
            self::SUBSCRIPTION_EXPIRING => 'Subscription Expiring Soon',
            self::SUBSCRIPTION_EXPIRED => 'Subscription Expired',

            self::STOCK_RECONCILIATION_COMPLETED => 'Stock Reconciliation Completed',
            self::DAMAGED_STOCK_RECORDED => 'Damaged Stock Recorded',
            self::LOST_STOCK_RECORDED => 'Lost Stock Recorded',
            self::EXPIRED_STOCK_PROCESSED => 'Expired Stock Disposed',
            self::BATCH_BLOCKED => 'Medicine Batch Blocked',

            self::STAFF_CREATED => 'Staff Account Created',
            self::STAFF_DEACTIVATED => 'Staff Account Deactivated',
            self::STAFF_ROLE_CHANGED => 'Staff Role Updated',
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

            self::LOW_STOCK => '📉',
            self::OUT_OF_STOCK => '🚫',
            self::EXPIRING_BATCH => '⏳',
            self::EXPIRED_BATCH => '❌',
            self::CUSTOMER_OUTSTANDING => '⏰',
            self::SUPPLIER_OUTSTANDING => '📑',
            self::CUSTOMER_PAYMENT => '💵',
            self::SUPPLIER_PAYMENT => '💸',
            self::SALE_COMPLETED => '🧾',
            self::PURCHASE_COMPLETED => '🛒',
            self::SALES_RETURN_COMPLETED => '↩️',
            self::PURCHASE_RETURN_COMPLETED => '↪️',
            self::EXPENSE_CREATED => '🏷️',
            self::SUBSCRIPTION_EXPIRING => '⌛',
            self::SUBSCRIPTION_EXPIRED => '🔒',

            self::STOCK_RECONCILIATION_COMPLETED => '⚖️',
            self::DAMAGED_STOCK_RECORDED => '💥',
            self::LOST_STOCK_RECORDED => '❓',
            self::EXPIRED_STOCK_PROCESSED => '🗑️',
            self::BATCH_BLOCKED => '⛔',
            self::STAFF_CREATED => '👨‍⚕️',
            self::STAFF_DEACTIVATED => '🚫',
            self::STAFF_ROLE_CHANGED => '🛡️',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::GENERAL => 'bg-slate-100 text-slate-700 border-slate-200',
            self::ANNOUNCEMENT => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::SUBSCRIPTION, self::SUBSCRIPTION_EXPIRING => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::SUBSCRIPTION_EXPIRED, self::EXPIRED_BATCH, self::BATCH_BLOCKED, self::STAFF_DEACTIVATED => 'bg-rose-100 text-rose-800 border-rose-300 font-bold',
            self::PAYMENT, self::CUSTOMER_PAYMENT => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::SUPPLIER_PAYMENT => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            self::SYSTEM => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            self::MAINTENANCE => 'bg-amber-50 text-amber-700 border-amber-200',
            self::WARNING, self::LOW_STOCK, self::EXPIRING_BATCH, self::DAMAGED_STOCK_RECORDED, self::LOST_STOCK_RECORDED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::OUT_OF_STOCK, self::EXPIRED_STOCK_PROCESSED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::CUSTOMER_OUTSTANDING => 'bg-purple-50 text-purple-700 border-purple-200',
            self::SUPPLIER_OUTSTANDING => 'bg-violet-50 text-violet-700 border-violet-200',
            self::SALE_COMPLETED => 'bg-blue-50 text-blue-700 border-blue-200',
            self::PURCHASE_COMPLETED, self::STOCK_RECONCILIATION_COMPLETED => 'bg-teal-50 text-teal-700 border-teal-200',
            self::SALES_RETURN_COMPLETED, self::PURCHASE_RETURN_COMPLETED => 'bg-orange-50 text-orange-700 border-orange-200',
            self::EXPENSE_CREATED => 'bg-slate-100 text-slate-700 border-slate-200',
            self::STAFF_CREATED, self::STAFF_ROLE_CHANGED => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::LOW_STOCK, self::OUT_OF_STOCK, self::EXPIRING_BATCH, self::EXPIRED_BATCH,
            self::STOCK_RECONCILIATION_COMPLETED, self::DAMAGED_STOCK_RECORDED, self::LOST_STOCK_RECORDED,
            self::EXPIRED_STOCK_PROCESSED, self::BATCH_BLOCKED => 'inventory',

            self::CUSTOMER_PAYMENT, self::SUPPLIER_PAYMENT, self::CUSTOMER_OUTSTANDING, self::SUPPLIER_OUTSTANDING => 'payments',
            self::SALE_COMPLETED, self::PURCHASE_COMPLETED, self::SALES_RETURN_COMPLETED, self::PURCHASE_RETURN_COMPLETED, self::EXPENSE_CREATED => 'transactions',
            self::SUBSCRIPTION, self::SUBSCRIPTION_EXPIRING, self::SUBSCRIPTION_EXPIRED => 'subscription',
            self::STAFF_CREATED, self::STAFF_DEACTIVATED, self::STAFF_ROLE_CHANGED => 'staff',
            default => 'system',
        };
    }

    public function defaultPriority(): NotificationPriority
    {
        return match ($this) {
            self::EXPIRED_BATCH, self::SUBSCRIPTION_EXPIRED, self::BATCH_BLOCKED, self::STAFF_DEACTIVATED => NotificationPriority::CRITICAL,
            self::LOW_STOCK, self::OUT_OF_STOCK, self::EXPIRING_BATCH, self::SUBSCRIPTION_EXPIRING, self::WARNING, self::DAMAGED_STOCK_RECORDED, self::LOST_STOCK_RECORDED, self::EXPIRED_STOCK_PROCESSED => NotificationPriority::HIGH,
            self::CUSTOMER_OUTSTANDING, self::SUPPLIER_OUTSTANDING, self::CUSTOMER_PAYMENT, self::SUPPLIER_PAYMENT, self::PURCHASE_COMPLETED, self::ANNOUNCEMENT, self::SUBSCRIPTION, self::PAYMENT, self::STOCK_RECONCILIATION_COMPLETED, self::STAFF_CREATED, self::STAFF_ROLE_CHANGED => NotificationPriority::NORMAL,
            default => NotificationPriority::LOW,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
