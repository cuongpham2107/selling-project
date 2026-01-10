<?php

namespace App\Filament\Resources\PointTransactions;

use App\Filament\Resources\PointTransactions\Pages\CreatePointTransaction;
use App\Filament\Resources\PointTransactions\Pages\ListPointTransactions;
use App\Models\PointTransaction;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PointTransactionResource extends Resource
{
    protected static ?string $model = PointTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý tài khoản';
    protected static ?string $navigationLabel = 'Lịch sử Point';

    protected static ?string $pluralLabel = 'Lịch sử Point';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\PointTransactions\Schemas\PointTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\PointTransactions\Tables\PointTransactionsTable::configure($table);
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
            'index' => ListPointTransactions::route('/'),
            'create' => CreatePointTransaction::route('/create'),
        ];
    }
}
