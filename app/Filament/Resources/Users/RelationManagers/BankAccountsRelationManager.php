<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\UserBankAccounts\Schemas\UserBankAccountForm;
use App\Filament\Resources\UserBankAccounts\Tables\UserBankAccountsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BankAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'bankAccounts';

    // protected static ?string $label = 'Tài khoản ngân hàng';
    protected static ?string $title = 'Tài khoản ngân hàng';

    public function form(Schema $schema): Schema
    {
        return UserBankAccountForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return UserBankAccountsTable::configure($table);
    }
}
