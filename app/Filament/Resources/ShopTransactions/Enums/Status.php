<?php
namespace App\Filament\Resources\ShopTransactions\Enums;

use Filament\Support\Contracts\HasLabel;
use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Illuminate\Contracts\Support\Htmlable;

enum Status: string implements HasLabel, HasIcon, HasColor
{
    case Pending = 'pending';
    case Held = 'held';
    case Completed = 'completed';
    case Disputed = 'disputed';
    case Cancelled = 'cancelled';
    
    public function getLabel(): string | Htmlable | null
    {
    
        return match ($this) {
            self::Pending => 'Đang chờ',
            self::Held => 'Đang giữ tiền',
            self::Completed => 'Hoàn thành',
            self::Disputed => 'Tranh chấp',
            self::Cancelled => 'Đã hủy',
        };
    }
     public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Held => 'warning',
            self::Completed => 'success',
            self::Disputed => 'danger',
            self::Cancelled => 'danger',
        };
    }
     public function getIcon(): string | BackedEnum | Htmlable | null
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::Held => 'heroicon-m-lock-closed',
            self::Completed => 'heroicon-m-check-circle',
            self::Disputed => 'heroicon-m-exclamation-triangle',
            self::Cancelled => 'heroicon-m-x-circle',
        };
    }
}