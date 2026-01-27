<?php

namespace App\Filament\Resources\UserBankAccounts\Pages;

use App\Filament\Resources\UserBankAccounts\UserBankAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserBankAccount extends EditRecord
{
    protected static string $resource = UserBankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
