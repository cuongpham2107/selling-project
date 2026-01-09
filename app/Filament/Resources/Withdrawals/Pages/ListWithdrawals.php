<?php

namespace App\Filament\Resources\Withdrawals\Pages;

use App\Filament\Resources\Withdrawals\WithdrawalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawals extends ListRecords
{
    protected static string $resource = WithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tạo yêu cầu rút tiền')
                ->slideOver()
                ->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    return $data;
                }),
        ];
    }
}
