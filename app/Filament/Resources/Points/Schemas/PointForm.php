<?php

namespace App\Filament\Resources\Points\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quản lý ví Point')
                ->description('Theo dõi và điều chỉnh số điểm hiện có của người dùng.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->label('Người dùng')
                                ->relationship('user', 'username')
                                ->required()
                                ->searchable(),
                            TextInput::make('points')
                                ->label('Số điểm hiện tại')
                                ->numeric()
                                ->default(0)
                                ->required(),
                        ]),
                ]),
        ]);
    }
}
