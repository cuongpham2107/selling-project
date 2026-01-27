<?php

namespace App\Filament\Resources\ShopTransactions\Pages;

use App\Filament\Resources\ShopTransactions\ShopTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShopTransactions extends ListRecords
{
    protected static string $resource = ShopTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
