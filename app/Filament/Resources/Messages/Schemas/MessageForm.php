<?php

namespace App\Filament\Resources\Messages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nội dung tin nhắn')
                ->description('Chi tiết về cuộc hội thoại và nội dung tin nhắn.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('chat_id')
                                ->label('Phòng chat')
                                ->relationship('chat', 'id')
                                ->required()
                                ->searchable(),
                            Select::make('sender_id')
                                ->label('Người gửi')
                                ->relationship('sender', 'username')
                                ->required()
                                ->searchable(),
                        ]),
                    Textarea::make('content')
                        ->label('Nội dung')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),
            Section::make('Đính kèm')
                ->description('Hình ảnh hoặc sản phẩm đi kèm với tin nhắn.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            FileUpload::make('image_url')
                                ->label('Ảnh đính kèm')
                                ->image()
                                ->maxSize(1024)
                                ->directory('messages'),
                            Select::make('product_id')
                                ->label('Sản phẩm đính kèm')
                                ->relationship('product', 'name')
                                ->searchable(),
                        ]),
                ]),
        ]);
    }
}
