<?php

namespace App\Filament\Resources\Deposits\Filters;

use App\Filament\Resources\Deposits\Enums\Status;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CustomDepositFilter extends Filter
{
    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this->label('Lọc nâng cao')
            ->form([
                Select::make('status')
                    ->label('Trạng thái')
                    ->options(Status::class)
                    ->placeholder('Tất cả trạng thái')
                    ->native(false),
                DatePicker::make('created_from')
                    ->label('Từ ngày')
                    ->placeholder('Chọn ngày bắt đầu')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()),
                DatePicker::make('created_to')
                    ->label('Đến ngày')
                    ->placeholder('Chọn ngày kết thúc')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now())
                    ->afterOrEqual('created_from'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['status'] ?? null,
                        fn (Builder $query, string $status): Builder => $query->where('status', $status)
                    )
                    ->when(
                        $data['created_from'] ?? null,
                        fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date)
                    )
                    ->when(
                        $data['created_to'] ?? null,
                        fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date)
                    );
            })
            ->columnSpan(2);
    }
}
