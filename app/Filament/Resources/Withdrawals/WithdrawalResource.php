<?php

namespace App\Filament\Resources\Withdrawals;

use App\Filament\Resources\Withdrawals\Pages\CreateWithdrawal;
use App\Filament\Resources\Withdrawals\Pages\EditWithdrawal;
use App\Filament\Resources\Withdrawals\Pages\ListWithdrawals;
use App\Models\Withdrawal;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class WithdrawalResource extends Resource
{
    protected static ?string $model = Withdrawal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý tài khoản';

    protected static ?string $navigationLabel = 'Rút tiền';

    protected static ?string $pluralLabel = 'Lịch sử rút tiền';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Withdrawals\Schemas\WithdrawalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Withdrawals\Tables\WithdrawalsTable::configure($table);
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
            'index' => ListWithdrawals::route('/'),
            // 'create' => CreateWithdrawal::route('/create'),
            // 'edit' => EditWithdrawal::route('/{record}/edit'),
        ];
    }
}
