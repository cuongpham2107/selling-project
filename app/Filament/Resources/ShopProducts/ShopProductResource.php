<?php

namespace App\Filament\Resources\ShopProducts;

use App\Filament\Resources\ShopProducts\Pages\CreateShopProduct;
use App\Filament\Resources\ShopProducts\Pages\EditShopProduct;
use App\Filament\Resources\ShopProducts\Pages\ListShopProducts;
use App\Filament\Resources\ShopProducts\Pages\ViewShopProduct;
use App\Filament\Resources\ShopProducts\Schemas\ShopProductForm;
use App\Filament\Resources\ShopProducts\Tables\ShopProductsTable;
use App\Models\ShopProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ShopProductResource extends Resource
{
    protected static ?string $model = ShopProduct::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý tài khoản';
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Sản phẩm';

    protected static ?string $pluralLabel = 'Sản phẩm';

    public static function form(Schema $schema): Schema
    {
        return ShopProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShopProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
            'index' => ListShopProducts::route('/'),
            'create' => CreateShopProduct::route('/create'),
            'view' => ViewShopProduct::route('/{record}'),
            'edit' => EditShopProduct::route('/{record}/edit'),
        ];
    }
}
