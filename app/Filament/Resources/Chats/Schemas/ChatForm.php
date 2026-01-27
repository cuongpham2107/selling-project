<?php

namespace App\Filament\Resources\Chats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cấu hình phòng chat')
                ->description('Xác định loại và quyền hạn của phòng chat này.')
                ->schema([
                    Select::make('type')
                        ->label('Loại phòng')
                        ->options([
                            'private_middle' => 'Riêng tư (Trung gian)',
                            'private_shop' => 'Riêng tư (Gian hàng)',
                            'general' => 'Tổng',
                        ])
                        ->required(),
                ]),
        ]);
    }
}
