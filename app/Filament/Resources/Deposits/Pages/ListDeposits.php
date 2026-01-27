<?php

namespace App\Filament\Resources\Deposits\Pages;

use App\Filament\Resources\Deposits\DepositResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListDeposits extends ListRecords
{
    protected static string $resource = DepositResource::class;

    protected function getHeaderActions(): array
    {

        return [
            Action::make('deposit')
                ->label('Nạp tiền')
                ->modalHeading('Nạp tiền vào ví')
                ->modalDescription('Bạn có thể nạp tiền vào ví của mình bằng cách sử dụng biểu mẫu bên dưới.')
                ->form([
                    TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->label('Số tiền nạp')
                        ->minValue(1)
                        ->maxValue(10000000)
                        ->default(100000)
                        ->columnSpan('full'),
                ])
                ->action(function (array $data, Action $action) {
                    $record = \App\Models\Deposit::create([
                        'user_id' => auth()->id(),
                        'amount' => $data['amount'],
                        'status' => 'pending',
                    ]);

                    return redirect()->route('sepay.redirect', ['deposit' => $record->id]);
                }),
        ];
    }
}
