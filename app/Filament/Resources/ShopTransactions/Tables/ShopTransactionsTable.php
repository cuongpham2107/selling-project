<?php

namespace App\Filament\Resources\ShopTransactions\Tables;

use App\Filament\Resources\ShopTransactions\Enums\Status;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ShopTransactionsTable
{
    public static function configure(Table $table): Table
    {
        $isSuperAdmin = auth()->user()->hasRole(config('filament-shield.super_admin.name'));
        $userId = auth()->id();
        
        return $table
            ->columns([
                TextColumn::make('transaction_type')
                    ->label('Loại')
                    ->hidden($isSuperAdmin)
                    ->badge()
                    ->color(fn ($record): string => $record?->buyer_id === $userId ? 'info' : 'success'),
                TextColumn::make('buyer.username')
                    ->label('Người mua')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state !== auth()->user()->username) {
                            return $state;
                        }
                        return "Bạn";
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seller.username')
                    ->label('Người bán')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state !== auth()->user()->username) {
                            return $state;
                        }
                        return "Bạn";
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Sản phẩm')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Tổng tiền')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),
                TextColumn::make('end_time')
                    ->label('Hạn chót')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày mua')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultGroup(
                $isSuperAdmin 
                    ? null 
                    : 'transaction_type'
            )
            ->groups([
                Group::make($isSuperAdmin ? '' : 'transaction_type')
                ->label('Loại giao dịch'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }
}
