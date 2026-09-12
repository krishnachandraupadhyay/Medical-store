<?php

namespace App\Enums;

enum MovementType: string
{
    case OPENING_STOCK = 'OPENING_STOCK';
    case ADJUSTMENT_IN = 'ADJUSTMENT_IN';
    case ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';
    case PURCHASE_IN = 'PURCHASE_IN';
    case STOCK_IN = 'STOCK_IN';
    case STOCK_OUT = 'STOCK_OUT';
    case SALE_OUT = 'SALE_OUT';
    case SALES_RETURN_IN = 'SALES_RETURN_IN';
    case PURCHASE_RETURN_OUT = 'PURCHASE_RETURN_OUT';
    case STOCK_RECONCILIATION_IN = 'STOCK_RECONCILIATION_IN';
    case STOCK_RECONCILIATION_OUT = 'STOCK_RECONCILIATION_OUT';
    case DAMAGED_STOCK_OUT = 'DAMAGED_STOCK_OUT';
    case LOST_STOCK_OUT = 'LOST_STOCK_OUT';
    case EXPIRED_STOCK_OUT = 'EXPIRED_STOCK_OUT';

    public function label(): string
    {
        return match ($this) {
            self::OPENING_STOCK => 'Opening Stock',
            self::ADJUSTMENT_IN => 'Stock Adjustment (In)',
            self::ADJUSTMENT_OUT => 'Stock Adjustment (Out)',
            self::PURCHASE_IN => 'Purchase Receipt',
            self::STOCK_IN => 'Stock In',
            self::STOCK_OUT => 'Stock Out',
            self::SALE_OUT => 'Sale (POS)',
            self::SALES_RETURN_IN => 'Sales Return',
            self::PURCHASE_RETURN_OUT => 'Purchase Return',
            self::STOCK_RECONCILIATION_IN => 'Stock Reconciliation (Surplus)',
            self::STOCK_RECONCILIATION_OUT => 'Stock Reconciliation (Shortage)',
            self::DAMAGED_STOCK_OUT => 'Damaged Stock Disposal',
            self::LOST_STOCK_OUT => 'Lost / Discrepancy Stock Out',
            self::EXPIRED_STOCK_OUT => 'Expired Stock Disposal',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::OPENING_STOCK => 'bg-blue-50 text-[#4b55c8] border-blue-200',
            self::PURCHASE_IN, self::ADJUSTMENT_IN, self::STOCK_IN, self::SALES_RETURN_IN, self::STOCK_RECONCILIATION_IN => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::ADJUSTMENT_OUT, self::STOCK_OUT, self::SALE_OUT, self::PURCHASE_RETURN_OUT, self::STOCK_RECONCILIATION_OUT, self::DAMAGED_STOCK_OUT, self::LOST_STOCK_OUT, self::EXPIRED_STOCK_OUT => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public function isAddition(): bool
    {
        return in_array($this, [
            self::OPENING_STOCK,
            self::PURCHASE_IN,
            self::ADJUSTMENT_IN,
            self::STOCK_IN,
            self::SALES_RETURN_IN,
            self::STOCK_RECONCILIATION_IN,
        ], true);
    }
}
