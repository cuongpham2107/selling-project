<?php

namespace App\Filament\Resources\FeeTiers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeeTiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_amount')
                    ->label('Tối thiểu')
                    ->money('VND'),
                TextColumn::make('max_amount')
                    ->label('Tối đa')
                    ->money('VND'),
                TextColumn::make('fee')
                    ->label('Phí')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->badge(),
            ])
            ->recordActions([
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
