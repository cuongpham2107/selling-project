<?php

namespace App\Filament\Resources\Deposits\Pages;

use App\Filament\Resources\Deposits\DepositResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDeposit extends CreateRecord
{
    protected static string $resource = DepositResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        $data['user_id'] = $user->id;
        $user->balance()->increment($data['amount']);
        dd($user);

        return static::getModel()::create($data);
    }
}
