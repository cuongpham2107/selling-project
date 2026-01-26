<?php

namespace App\Filament\Resources\BalanceTransactions;

use App\Filament\Resources\BalanceTransactions\Pages\CreateBalanceTransaction;
use App\Filament\Resources\BalanceTransactions\Pages\EditBalanceTransaction;
use App\Filament\Resources\BalanceTransactions\Pages\ListBalanceTransactions;
use App\Filament\Resources\BalanceTransactions\Pages\ViewBalanceTransaction;
use App\Filament\Resources\BalanceTransactions\Schemas\BalanceTransactionForm;
use App\Filament\Resources\BalanceTransactions\Tables\BalanceTransactionsTable;
use App\Models\BalanceTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BalanceTransactionResource extends Resource
{
    protected static ?string $model = BalanceTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'amount';

    protected static string|UnitEnum|null $navigationGroup = 'Tài chính';

    protected static ?string $pluralLabel = 'Lịch sử giao dịch';

    protected static ?string $modelLabel = 'Giao dịch tiền';

    protected static ?string $pluralModelLabel = 'Lịch sử giao dịch';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BalanceTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BalanceTransactionsTable::configure($table);
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
            'index' => ListBalanceTransactions::route('/'),
            'create' => CreateBalanceTransaction::route('/create'),
            'view' => ViewBalanceTransaction::route('/{record}'),
            'edit' => EditBalanceTransaction::route('/{record}/edit'),
        ];
    }
}
