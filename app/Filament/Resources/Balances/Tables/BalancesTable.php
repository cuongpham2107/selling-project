<?php

namespace App\Filament\Resources\Balances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('Người dùng')
                    ->hidden(fn ($record) => ! auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('Số dư')
                    ->money('VND')
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
