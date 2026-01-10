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
                    ->label('Số tiền tối thiểu')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('max_amount')
                    ->label('Số tiền tối đa')
                    ->money('VND')
                    ->sortable()
                    ->placeholder('Không giới hạn'),
                TextColumn::make('fee')
                    ->label('Mức phí')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('amount_range')
                    ->label('Khoảng tiền')
                    ->state(function ($record) {
                        $min = number_format($record->min_amount, 0, ',', '.');
                        $max = $record->max_amount 
                            ? number_format($record->max_amount, 0, ',', '.') 
                            : '∞';
                        return "{$min} - {$max} VNĐ";
                    })
                    ->searchable(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('min_amount', 'asc')
            ->defaultPaginationPageOption(25);
    }
}
