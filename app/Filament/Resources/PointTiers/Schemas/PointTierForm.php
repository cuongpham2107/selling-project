<?php

namespace App\Filament\Resources\PointTiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PointTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cấu hình thưởng Point')
                ->description('Thiết lập các ngưỡng số tiền và số điểm thưởng tương ứng.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('min_amount')
                                ->label('Số tiền tối thiểu')
                                ->numeric()
                                ->prefix('VNĐ'),
                            TextInput::make('max_amount')
                                ->label('Số tiền tối đa')
                                ->numeric()
                                ->prefix('VNĐ'),
                            TextInput::make('points')
                                ->label('Điểm thưởng')
                                ->numeric()
                                ->required(),
                        ]),
                ]),
        ]);
    }
}
