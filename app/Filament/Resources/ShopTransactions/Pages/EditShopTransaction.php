<?php

namespace App\Filament\Resources\ShopTransactions\Pages;

use App\Filament\Resources\ShopTransactions\ShopTransactionResource;
use App\Models\ShopTransaction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShopTransaction extends EditRecord
{
    protected static string $resource = ShopTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('completed')
            //     ->label('Hoàn thành')
            //     ->color('primary')
            //     ->icon('heroicon-o-check-circle')
            //     ->action(function (ShopTransaction $record) {
            //         $record->status = 'completed';
            //         $record->save();
            //     }),
            // // Tranh chấp
            // Action::make('dispute')
            //     ->label('Tranh chấp')
            //     ->color('danger')
            //     ->icon('heroicon-o-exclamation-triangle')
            //     ->action(function (ShopTransaction $record) {
            //         $record->status = 'disputed';
            //         $record->save();
            //     }),
            // ViewAction::make(),
            // DeleteAction::make(),

        ];
    }
}
