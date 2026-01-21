<?php

namespace App\Filament\Resources\ShopProducts\Pages;

use App\Filament\Resources\ShopProducts\ShopProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditShopProduct extends EditRecord
{
    protected static string $resource = ShopProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_sell_product')
                ->label('Mở bán sản phẩm')
                ->icon('heroicon-o-lock-open')
                ->visible(fn ($record) => $record->status === 'sold')

                ->action(function ($record) {
                    $record->update(['status' => 'active']);
                    Notification::make()
                        ->title('Mở bán lại thành công')
                        ->body('Sản phẩm đã được mở bán lại.')
                        ->success()
                        ->send();
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
