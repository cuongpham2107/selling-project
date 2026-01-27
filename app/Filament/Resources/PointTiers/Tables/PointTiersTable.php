<?php

namespace App\Filament\Resources\PointTiers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PointTiersTable
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
                TextColumn::make('points')
                    ->label('Điểm thưởng')
                    ->sortable(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }
}
