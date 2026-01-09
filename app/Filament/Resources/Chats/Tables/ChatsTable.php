<?php

namespace App\Filament\Resources\Chats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'private_middle' => 'Riêng tư (Trung gian)',
                        'private_shop' => 'Riêng tư (Gian hàng)',
                        'general' => 'Tổng',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'private_middle' => 'success',
                        'private_shop' => 'warning',
                        'general' => 'danger',
                        default => 'gray',
                    })
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
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
