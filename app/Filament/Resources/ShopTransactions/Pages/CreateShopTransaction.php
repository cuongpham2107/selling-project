<?php

namespace App\Filament\Resources\ShopTransactions\Pages;

use App\Filament\Resources\ShopTransactions\ShopTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShopTransaction extends CreateRecord
{
    protected static string $resource = ShopTransactionResource::class;

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['buyer_id'] = auth()->id();
    //     $data['fee'] = ceil($data['amount'] * 0.01); // 1% fee fixed for shop
    //     $data['status'] = 'held';

    //     $buyerBalance = auth()->user()->balance;

    //     if ($buyerBalance->balance < $data['amount']) {
    //          \Filament\Notifications\Notification::make()
    //             ->title('Số dư không đủ')
    //             ->body('Số dư của bạn không đủ để thực hiện giao dịch này.')
    //             ->danger()
    //             ->send();

    //         $this->halt();
    //     }

    //     \Illuminate\Support\Facades\DB::transaction(function () use ($buyerBalance, $data) {
    //         $buyerBalance->decrement('balance', $data['amount']);
    //         $buyerBalance->increment('held_balance', $data['amount']);
    //     });

    //     return $data;
    // }
}
