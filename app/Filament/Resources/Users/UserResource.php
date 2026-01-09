<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\RelationManagers\BankAccountsRelationManager;
use App\Filament\Resources\Users\RelationManagers\ShopTransactionsAsBuyerRelationManager;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'username';

    protected static string|UnitEnum|null $navigationGroup = 'Cấu hình hệ thống';

    protected static ?string $navigationLabel = 'Người dùng';


    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BankAccountsRelationManager::class,
            ShopTransactionsAsBuyerRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        
        $query = parent::getEloquentQuery();
       
        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            $query->where('id', auth()->id());
        }
        
        return $query;
    }

    public static function getNavigationUrl(): string
    {
        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            return static::getUrl('edit', ['record' => auth()->id()]);
        }
        
        return parent::getNavigationUrl();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
