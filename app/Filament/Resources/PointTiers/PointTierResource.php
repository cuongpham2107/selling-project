<?php

namespace App\Filament\Resources\PointTiers;

use App\Filament\Resources\PointTiers\Pages\CreatePointTier;
use App\Filament\Resources\PointTiers\Pages\EditPointTier;
use App\Filament\Resources\PointTiers\Pages\ListPointTiers;
use App\Models\PointTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PointTierResource extends Resource
{
    protected static ?string $model = PointTier::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup = 'Cấu hình hệ thống';

    protected static ?string $navigationLabel = 'Bảng thưởng Point';

    protected static ?string $pluralLabel = 'Bảng thưởng Point';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\PointTiers\Schemas\PointTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\PointTiers\Tables\PointTiersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPointTiers::route('/'),
            'create' => CreatePointTier::route('/create'),
            'edit' => EditPointTier::route('/{record}/edit'),
        ];
    }
}
