<?php

namespace App\Filament\Resources\ShopProducts\Pages;

use App\Filament\Resources\ShopProducts\ShopProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShopProduct extends CreateRecord
{
    protected static string $resource = ShopProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
