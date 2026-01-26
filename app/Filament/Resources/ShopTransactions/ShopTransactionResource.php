<?php

namespace App\Filament\Resources\ShopTransactions;

use App\Filament\Resources\ShopTransactions\Pages\CreateShopTransaction;
use App\Filament\Resources\ShopTransactions\Pages\EditShopTransaction;
use App\Filament\Resources\ShopTransactions\Pages\ListShopTransactions;
use App\Filament\Resources\ShopTransactions\Pages\ViewShopTransaction;
use App\Filament\Resources\ShopTransactions\Schemas\ShopTransactionForm;
use App\Filament\Resources\ShopTransactions\Tables\ShopTransactionsTable;
use App\Models\ShopTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ShopTransactionResource extends Resource
{
    protected static ?string $model = ShopTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|UnitEnum|null $navigationGroup = 'Mua bán';

    protected static ?string $navigationLabel = 'Đơn hàng';

    protected static ?string $pluralLabel = 'Đơn hàng';

    protected static ?string $modelLabel = 'Đơn hàng';

    public static function form(Schema $schema): Schema
    {
        return ShopTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShopTransactionsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            $query->where(function ($q) {
                $q->where('buyer_id', auth()->id())
                    ->orWhere('seller_id', auth()->id());
            });

            // Sắp xếp status theo thứ tự 'pending', 'held', 'completed', 'disputed', 'cancelled'
            // Using CASE WHEN for SQLite compatibility
            $query->orderByRaw("
                CASE status
                    WHEN 'pending' THEN 1
                    WHEN 'held' THEN 2
                    WHEN 'completed' THEN 3
                    WHEN 'disputed' THEN 4
                    WHEN 'cancelled' THEN 5
                    ELSE 6
                END
            ");
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShopTransactions::route('/'),
            // 'create' => CreateShopTransaction::route('/create'),
            'view' => ViewShopTransaction::route('/{record}'),
            'edit' => EditShopTransaction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            return static::getModel()::where('buyer_id', auth()->id())
                ->orWhere('seller_id', auth()->id())
                ->where('status', 'pending')
                ->count();
        }

        return null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Đơn hàng mới cần xử lý';
    }
}
