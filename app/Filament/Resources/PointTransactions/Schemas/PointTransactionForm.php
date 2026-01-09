<?php

namespace App\Filament\Resources\PointTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PointTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin giao dịch')
                ->description('Chi tiết về số lượng và loại giao dịch Point.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('amount')
                                ->label('Số lượng')
                                ->numeric()
                                ->required(),
                            Select::make('type')
                                ->label('Phân loại')
                                ->options([
                                    'reward' => 'Thưởng',
                                    'transfer' => 'Chuyển',
                                    'spent' => 'Chi tiêu',
                                ]),
                        ]),
                ]),
            Section::make('Đối tượng liên quan')
                ->description('Người gửi và người nhận trong giao dịch này.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label('Người dùng')
                                ->relationship('user', 'username')
                                ->required()
                                ->searchable(),
                            Select::make('recipient_id')
                                ->label('Người nhận (nếu có)')
                                ->relationship('recipient', 'username')
                                ->searchable(),
                        ]),
                ]),
        ]);
    }
}
