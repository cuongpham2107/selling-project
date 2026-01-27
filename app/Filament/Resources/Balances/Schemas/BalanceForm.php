<?php

namespace App\Filament\Resources\Balances\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BalanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quản lý số dư')
                ->description('Theo dõi và điều chỉnh số dư tiền mặt của người dùng.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label('Người dùng')
                                ->relationship('user', 'username')
                                ->required()
                                ->searchable(),
                            TextInput::make('balance')
                                ->label('Số dư hiện tại')
                                ->numeric()
                                ->prefix('VNĐ')
                                ->default(0)
                                ->required(),
                        ]),
                ]),
        ]);
    }
}
