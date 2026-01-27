<?php

namespace App\Filament\Resources\BalanceTransactions\Pages;

use App\Filament\Resources\BalanceTransactions\BalanceTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBalanceTransaction extends CreateRecord
{
    protected static string $resource = BalanceTransactionResource::class;
}
