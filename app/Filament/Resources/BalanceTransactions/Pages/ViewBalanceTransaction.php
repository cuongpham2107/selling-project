<?php

namespace App\Filament\Resources\BalanceTransactions\Pages;

use App\Filament\Resources\BalanceTransactions\BalanceTransactionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBalanceTransaction extends ViewRecord
{
    protected static string $resource = BalanceTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
