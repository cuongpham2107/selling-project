<?php

namespace App\Filament\Resources\Balances;

use App\Filament\Resources\Balances\Pages\CreateBalance;
use App\Filament\Resources\Balances\Pages\EditBalance;
use App\Filament\Resources\Balances\Pages\ListBalances;
use App\Filament\Resources\Balances\Pages\ViewBalance;
use App\Models\Balance;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý tài chính';

    protected static ?string $navigationLabel = 'Số dư ví';

    protected static ?string $pluralLabel = 'Số dư ví';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Balances\Schemas\BalanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Balances\Tables\BalancesTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            $query->where('user_id', auth()->id());
        }
        
        return $query;
    }

    public static function canAccess(): bool
    {
        if (auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            return true;
        }
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBalances::route('/'),
            'create' => CreateBalance::route('/create'),
            'view' => ViewBalance::route('/{record}'),
            'edit' => EditBalance::route('/{record}/edit'),
        ];
    }
}
