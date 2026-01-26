<?php

namespace App\Filament\Resources\Points;

use App\Filament\Resources\Points\Pages\CreatePoint;
use App\Filament\Resources\Points\Pages\EditPoint;
use App\Filament\Resources\Points\Pages\ListPoints;
use App\Models\Point;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PointResource extends Resource
{
    protected static ?string $model = Point::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup = 'Quản lý tài khoản';

    protected static ?string $navigationLabel = 'Ví Point';

    protected static ?string $pluralLabel = 'Ví Point';

    protected static ?string $modelLabel = 'Ví Point';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Points\Schemas\PointForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Points\Tables\PointsTable::configure($table);
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
            'index' => ListPoints::route('/'),
            'create' => CreatePoint::route('/create'),
            'edit' => EditPoint::route('/{record}/edit'),

        ];
    }
}
