<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case ONLINE = 'online';
    case UPI = 'upi';
    case CARD = 'card';
    case NET_BANKING = 'net_banking';
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case CHEQUE = 'cheque';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE => 'Online',
            self::UPI => 'UPI',
            self::CARD => 'Card (Credit/Debit)',
            self::NET_BANKING => 'Net Banking',
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer (NEFT/IMPS)',
            self::CHEQUE => 'Cheque',
            self::OTHER => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
