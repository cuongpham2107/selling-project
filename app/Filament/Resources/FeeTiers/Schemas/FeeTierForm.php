<?php

namespace App\Filament\Resources\FeeTiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeeTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cấu hình phí giao dịch trung gian')
                ->description('Thiết lập các ngưỡng số tiền và mức phí tương ứng cho giao dịch trung gian. Phí gian hàng (1%) được cấu hình trong config/transaction.php')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('min_amount')
                                ->label('Số tiền tối thiểu')
                                ->numeric()
                                ->required()
                                ->suffix('VNĐ')
                                ->minValue(0),
                            TextInput::make('max_amount')
                                ->label('Số tiền tối đa')
                                ->numeric()
                                ->suffix('VNĐ')
                                ->helperText('Để trống nếu không giới hạn trên')
                                ->minValue(0),
                            TextInput::make('fee')
                                ->label('Mức phí cố định')
                                ->numeric()
                                ->required()
                                ->suffix('VNĐ')
                                ->minValue(0)
                                ->helperText('Phí cố định cho khoảng tiền này'),
                        ]),
                ]),
        ]);
    }
}
