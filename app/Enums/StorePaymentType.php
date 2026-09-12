<?php

namespace App\Enums;

enum StorePaymentType: string
{
    case SALE_PAYMENT = 'SALE_PAYMENT';
    case SALE_REFUND = 'SALE_REFUND';
    case PURCHASE_PAYMENT = 'PURCHASE_PAYMENT';
    case EXPENSE_PAYMENT = 'EXPENSE_PAYMENT';

    public function label(): string
    {
        return match ($this) {
            self::SALE_PAYMENT => 'Customer Sale Payment',
            self::SALE_REFUND => 'Customer Sale Refund',
            self::PURCHASE_PAYMENT => 'Supplier Purchase Payment',
            self::EXPENSE_PAYMENT => 'Expense Payment',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::SALE_PAYMENT => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::SALE_REFUND => 'bg-amber-50 text-amber-800 border-amber-200',
            self::PURCHASE_PAYMENT => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::EXPENSE_PAYMENT => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
