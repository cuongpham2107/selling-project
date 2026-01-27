<?php

namespace App\Filament\Resources\UserBankAccounts\Pages;

use App\Filament\Resources\UserBankAccounts\UserBankAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserBankAccount extends CreateRecord
{
    protected static string $resource = UserBankAccountResource::class;
}
