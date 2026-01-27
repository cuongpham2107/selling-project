<?php

namespace App\Filament\Resources\Balances\Pages;

use App\Filament\Resources\Balances\BalanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBalance extends ViewRecord
{
    protected static string $resource = BalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
