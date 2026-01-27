<?php

namespace App\Filament\Resources\Deposits\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Method: string implements HasLabel
{
    case BankTransfer = 'bank_transfer';
    case CreditCard = 'credit_card';
    case PayPal = 'paypal';
    case Bitcoin = 'bitcoin';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::BankTransfer => 'Chuyển khoản ngân hàng',
            self::CreditCard => 'Thẻ tín dụng (Chưa hỗ trợ)',
            self::PayPal => 'PayPal (Chưa hỗ trợ)',
            self::Bitcoin => 'Bitcoin (Chưa hỗ trợ)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BankTransfer => 'blue',
            self::CreditCard => 'green',
            self::PayPal => 'yellow',
            self::Bitcoin => 'orange',
        };
    }

    public function getIcon(): string|Htmlable|null
    {
        return match ($this) {
            self::BankTransfer => 'heroicon-m-building-library',
            self::CreditCard => 'heroicon-m-credit-card',
            self::PayPal => 'heroicon-m-currency-dollar',
            self::Bitcoin => 'heroicon-m-currency-bitcoin',
        };
    }
}
