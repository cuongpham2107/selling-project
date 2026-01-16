<?php

namespace App\Filament\Resources\UserBankAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserBankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bank_name')
                    ->hidden(),
                View::make('user-bank-accounts.table.custom-row-content'),
            ])
            ->contentGrid([
                '' => 1,
                'sm' => 2,
                'md' => 3,
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->slideOver()
                    ->modal(),
            ])
            ->defaultSort('is_default', 'desc')
            ->bulkActions([
            ])
            ->defaultPaginationPageOption(25);
    }
}
