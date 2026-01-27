<?php

namespace App\Filament\Resources\BalanceTransactions\Pages;

use App\Filament\Resources\BalanceTransactions\BalanceTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBalanceTransaction extends EditRecord
{
    protected static string $resource = BalanceTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
