<?php

namespace App\Filament\Resources\Deposits\Pages;

use App\Filament\Resources\Deposits\DepositResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nạp tiền')
                ->slideOver()
                ->modalHeading('Nạp tiền vào ví')
                ->modalDescription('Bạn có thể nạp tiền vào ví của mình bằng cách sử dụng biểu mẫu bên dưới.')
                ->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    $data['status'] = 'pending';

                    return $data;
                }),
        ];
    }
}
