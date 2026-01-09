<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\UserBankAccounts\Tables\UserBankAccountsTable;
use App\Filament\Resources\UserBankAccounts\Schemas\UserBankAccountForm;

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
