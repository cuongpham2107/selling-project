<?php

namespace App\Filament\Resources\Deposits;

use App\Filament\Resources\Deposits\Pages\CreateDeposit;
use App\Filament\Resources\Deposits\Pages\EditDeposit;
use App\Filament\Resources\Deposits\Pages\ListDeposits;
use App\Models\Deposit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý tài chính';

    protected static ?string $navigationLabel = 'Nạp tiền';

    protected static ?string $pluralLabel = 'Lịch sử nạp tiền';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Deposits\Schemas\DepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Deposits\Tables\DepositsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeposits::route('/'),
            // 'create' => CreateDeposit::route('/create'),
            // 'edit' => EditDeposit::route('/{record}/edit'),
        ];
    }
}
