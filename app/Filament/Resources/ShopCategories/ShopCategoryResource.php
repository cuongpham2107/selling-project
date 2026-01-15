<?php

namespace App\Filament\Resources\ShopCategories;

use App\Filament\Resources\ShopCategories\Pages\CreateShopCategory;
use App\Filament\Resources\ShopCategories\Pages\EditShopCategory;
use App\Filament\Resources\ShopCategories\Pages\ListShopCategories;
use App\Filament\Resources\ShopCategories\Schemas\ShopCategoryForm;
use App\Filament\Resources\ShopCategories\Tables\ShopCategoriesTable;
use App\Models\ShopCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ShopCategoryResource extends Resource
{
    protected static ?string $model = ShopCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý Gian hàng';

    protected static ?string $navigationLabel = 'Danh mục';

    protected static ?string $pluralLabel = 'Danh mục';

    protected static ?string $modelLabel = 'Danh mục';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ShopCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShopCategoriesTable::configure($table);
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
            'index' => ListShopCategories::route('/'),
            'create' => CreateShopCategory::route('/create'),
            'edit' => EditShopCategory::route('/{record}/edit'),
        ];
    }
}
