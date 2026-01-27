<?php

namespace App\Filament\Resources\Deposits\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Status: string implements HasLabel
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'Chờ duyệt',
            self::Completed => 'Thành công',
            self::Failed => 'Thất bại',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }

    public function getIcon(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'heroicon-m-building-library',
            self::Completed => 'heroicon-m-check-circle',
            self::Failed => 'heroicon-m-x-circle',
        };
    }
}
