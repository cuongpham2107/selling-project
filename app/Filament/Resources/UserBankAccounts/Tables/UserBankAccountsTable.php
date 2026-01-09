<?php

namespace App\Filament\Resources\UserBankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserBankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.username')
                    ->label('Người dùng')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn () => ! auth()->user()->hasRole(config('filament-shield.super_admin.name'))),

                TextColumn::make('bank_name')
                    ->label('Ngân hàng')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('account_holder_name')
                    ->label('Chủ tài khoản')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('account_number')
                    ->label('Số tài khoản')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Đã sao chép số tài khoản')
                    ->copyMessageDuration(1500),

                IconColumn::make('is_default')
                    ->label('Mặc định')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->slideOver(),
            ])
            ->defaultSort('is_default', 'desc')
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }
}
