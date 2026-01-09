<?php

namespace App\Filament\Resources\FeeTiers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeeTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cấu hình phí giao dịch')
                ->description('Thiết lập các ngưỡng số tiền và mức phí tương ứng cho từng loại giao dịch.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('min_amount')
                                ->label('Số tiền tối thiểu')
                                ->numeric()
                                ->prefix('VNĐ'),
                            TextInput::make('max_amount')
                                ->label('Số tiền tối đa')
                                ->numeric()
                                ->prefix('VNĐ'),
                            TextInput::make('fee')
                                ->label('Mức phí')
                                ->numeric()
                                ->required(),
                            Select::make('type')
                                ->label('Loại giao dịch')
                                ->options([
                                    'middle' => 'Trung gian',
                                    'shop' => 'Gian hàng',
                                ])
                                ->required(),
                        ]),
                ]),
        ]);
    }
}
