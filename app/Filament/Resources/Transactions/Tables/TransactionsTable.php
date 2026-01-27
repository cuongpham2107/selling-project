<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Filament\Tables\BaseTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return BaseTable::configure($table)
            ->columns([
                TextColumn::make('buyer.username')
                    ->label('Người mua')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seller.username')
                    ->label('Người bán')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Số tiền')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'seller_sent' => 'Người bán đã gửi',
                        'buyer_received' => 'Người mua đã nhận',
                        'completed' => 'Hoàn thành',
                        'disputed' => 'Tranh chấp',
                        'cancelled' => 'Đã hủy',
                        'overdue' => 'Quá hạn',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'info',
                        'seller_sent' => 'warning',
                        'buyer_received' => 'success',
                        'completed' => 'success',
                        'disputed' => 'danger',
                        'cancelled' => 'danger',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('end_time')
                    ->label('Thời hạn')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                BaseTable::getCreatedAtColumn()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                BaseTable::getCreatedAtFilter(),
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
