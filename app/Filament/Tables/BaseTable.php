<?php

namespace App\Filament\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BaseTable
{
    /**
     * Configure common table settings.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->poll('60s');
    }

    /**
     * Get common Created At column.
     */
    public static function getCreatedAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label('Thời gian')
            ->dateTime('d/m/Y H:i')
            ->sortable()
            ->toggleable();
    }

    /**
     * Get common Updated At column.
     */
    public static function getUpdatedAtColumn(): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label('Cập nhật')
            ->dateTime('d/m/Y H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * Get common Created At date range filter.
     */
    public static function getCreatedAtFilter(): Filter
    {
        return Filter::make('created_at')
            ->label('Thời gian')
            ->form([
                DatePicker::make('created_from')->label('Từ ngày'),
                DatePicker::make('created_until')->label('Đến ngày'),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['created_from'],
                        fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                    )
                    ->when(
                        $data['created_until'],
                        fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                    );
            });
    }

    /**
     * Get common User filter.
     */
    public static function getUserFilter(): \Filament\Tables\Filters\SelectFilter
    {
        return \Filament\Tables\Filters\SelectFilter::make('user')
            ->label('Người dùng')
            ->relationship('user', 'username')
            ->searchable()
            ->preload();
    }

    /**
     * Get common Delete Bulk Action.
     */
    public static function getDeleteBulkAction(): BulkActionGroup
    {
        return BulkActionGroup::make([
            DeleteBulkAction::make(),
        ]);
    }
}
