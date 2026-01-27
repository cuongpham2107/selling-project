<?php

namespace App\Filament\Resources\Withdrawals\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Method: string implements HasLabel
{
    case BankTransfer = 'bank_transfer';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::BankTransfer => 'Chuyển khoản ngân hàng',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BankTransfer => 'blue',
        };
    }

    public function getIcon(): string|Htmlable|null
    {
        return match ($this) {
            self::BankTransfer => 'heroicon-m-building-library',
        };
    }
}
