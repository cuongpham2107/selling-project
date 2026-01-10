<?php

namespace App\Filament\Resources\PointTransactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PointTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('Người dùng')
                    ->hidden(fn ($record) => ! auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Số điểm')
                    ->alignCenter(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'earn' => 'Nâng',
                        'send' => 'Gửi',
                        'receive' => 'Nhận',
                        'redeem' => 'Trừ',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'earn' => 'success',
                        'send' => 'warning',
                        'receive' => 'success',
                        'redeem' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }
}
