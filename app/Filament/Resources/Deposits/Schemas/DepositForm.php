<?php

namespace App\Filament\Resources\Deposits\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class DepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin nạp tiền')
                ->description('Chi tiết về số tiền và phương thức nạp.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('amount')
                                ->label('Số tiền')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->disabled()
                                ->numeric()
                                ->prefix('VNĐ')
                                ->required(),
                            TextInput::make('method')
                                ->label('Phương thức')
                                ->disabled()
                                ->required(),
                            Select::make('status')
                                ->label('Trạng thái')
                                ->options([
                                    'pending' => 'Chờ duyệt',
                                    'completed' => 'Thành công',
                                    'failed' => 'Thất bại',
                                ])
                                ->default('pending')
                                ->disabled()
                                ->dehydrated()
                                ->required(),
                        ]),
                    Section::make('Chi tiết thanh toán SePay')
                        ->schema([
                            KeyValue::make('sepay_payload.data')
                                ->label('Thông tin giao dịch')
                                ->keyLabel('Trường')
                                ->valueLabel('Giá trị')
                                ->afterStateHydrated(function (KeyValue $component, $state) {
                                    if (! is_array($state)) {
                                        return;
                                    }

                                    $flattened = [];
                                    foreach ($state as $key => $value) {
                                        if (is_array($value)) {
                                            $flattened[$key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                        } else {
                                            $flattened[$key] = $value;
                                        }
                                    }

                                    $component->state($flattened);
                                })
                                ->disabled(),
                        ])
                        ->visible(fn ($record) => $record && isset($record->sepay_payload['data']))
                        ->collapsible(),
                ])->columnSpanFull(),
        ]);
    }
}
