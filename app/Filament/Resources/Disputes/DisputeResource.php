<?php

namespace App\Filament\Resources\Disputes;

use App\Filament\Resources\Disputes\Pages\CreateDispute;
use App\Filament\Resources\Disputes\Pages\EditDispute;
use App\Filament\Resources\Disputes\Pages\ListDisputes;
use App\Models\Dispute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DisputeResource extends Resource
{
    protected static ?string $model = Dispute::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|UnitEnum|null $navigationGroup = 'Hệ thống hỗ trợ';

    protected static ?string $navigationLabel = 'Tranh chấp';

    protected static ?string $pluralLabel = 'Tranh chấp';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Disputes\Schemas\DisputeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Disputes\Tables\DisputesTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()->hasRole(config('filament-shield.super_admin.name'))) {
            $query->where(function ($q) {
                $q->where('initiator_id', auth()->id())
                    ->orWhereHasMorph('transaction', [\App\Models\Transaction::class, \App\Models\ShopTransaction::class], function ($query) {
                        $query->where('buyer_id', auth()->id())
                            ->orWhere('seller_id', auth()->id());
                    });
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDisputes::route('/'),
            'create' => CreateDispute::route('/create'),
            'edit' => EditDispute::route('/{record}/edit'),
        ];
    }
}
