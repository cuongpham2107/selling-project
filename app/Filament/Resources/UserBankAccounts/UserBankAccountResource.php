<?php

namespace App\Filament\Resources\UserBankAccounts;

use App\Filament\Resources\UserBankAccounts\Pages\CreateUserBankAccount;
use App\Filament\Resources\UserBankAccounts\Pages\EditUserBankAccount;
use App\Filament\Resources\UserBankAccounts\Pages\ListUserBankAccounts;
use App\Filament\Resources\UserBankAccounts\Schemas\UserBankAccountForm;
use App\Filament\Resources\UserBankAccounts\Tables\UserBankAccountsTable;
use App\Models\UserBankAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserBankAccountResource extends Resource
{
    protected static ?string $model = UserBankAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Tài khoản ngân hàng';

    protected static ?string $modelLabel = 'Tài khoản ngân hàng';

    protected static ?string $pluralLabel = 'Tài khoản ngân hàng';

    protected static ?string $pluralModelLabel = 'Tài khoản ngân hàng';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý tài khoản';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return UserBankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserBankAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserBankAccounts::route('/'),
            // 'create' => CreateUserBankAccount::route('/create'),
            'edit' => EditUserBankAccount::route('/{record}/edit'),
        ];
    }
}
