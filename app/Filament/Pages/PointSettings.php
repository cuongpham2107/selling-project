<?php

namespace App\Filament\Pages;

use App\Models\BalanceTransaction;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class PointSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected string $view = 'filament.pages.point-settings';

    protected static ?string $title = 'Thông tin công khai Point';

    protected static ?string $navigationLabel = 'Thống kê Point';

    public static function canAccess(): bool
    {
        return true;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PointSettings\PointStatsOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BalanceTransaction::query()->where('currency', 'point'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('Người dùng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn ($record): string => match ($record->type) {
                        'point_earn', 'point_receive', 'point_redeem' => 'success',
                        'point_send', 'redeem' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'point_earn' => 'Kiếm điểm',
                        'point_send' => 'Gửi điểm',
                        'point_receive' => 'Nhận điểm',
                        'redeem' => 'Quy đổi',
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label('Số lượng')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($record) => ($record->amount >= 0 ? '+' : '').
                        number_format((float) abs($record->amount), 2, ',', '.').
                        ' Point'
                    )
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
