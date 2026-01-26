<?php

namespace App\Filament\Resources\FeeTiers;

use App\Filament\Resources\FeeTiers\Pages\CreateFeeTier;
use App\Filament\Resources\FeeTiers\Pages\EditFeeTier;
use App\Filament\Resources\FeeTiers\Pages\ListFeeTiers;
use App\Models\FeeTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FeeTierResource extends Resource
{
    protected static ?string $model = FeeTier::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-variable';

    protected static string|UnitEnum|null $navigationGroup = 'Cấu hình hệ thống';

    protected static ?string $navigationLabel = 'Bảng phí giao dịch';

    protected static ?string $pluralLabel = 'Bảng phí giao dịch';

    protected static ?string $modelLabel = 'Bảng phí giao dịch';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\FeeTiers\Schemas\FeeTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\FeeTiers\Tables\FeeTiersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeeTiers::route('/'),
            'create' => CreateFeeTier::route('/create'),
            'edit' => EditFeeTier::route('/{record}/edit'),
        ];
    }
}
