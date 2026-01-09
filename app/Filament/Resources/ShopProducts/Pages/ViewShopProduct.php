<?php

namespace App\Filament\Resources\ShopProducts\Pages;

use App\Filament\Resources\ShopProducts\ShopProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShopProduct extends ViewRecord
{
    protected static string $resource = ShopProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
