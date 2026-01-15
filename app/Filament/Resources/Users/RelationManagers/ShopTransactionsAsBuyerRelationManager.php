<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\ShopTransactions\Schemas\ShopTransactionForm;
use App\Filament\Resources\ShopTransactions\Tables\ShopTransactionsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ShopTransactionsAsBuyerRelationManager extends RelationManager
{
    protected static string $relationship = 'shopTransactionsAsBuyer';

    protected static ?string $title = 'Đơn hàng đã mua';

    public function form(Schema $schema): Schema
    {
        return ShopTransactionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ShopTransactionsTable::configure($table);
    }
}
