<?php

namespace App\Filament\Resources\ShopProducts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShopProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin sản phẩm')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Tên sản phẩm')
                                    ->required(),
                                TextInput::make('price')
                                    ->label('Giá bán')
                                    ->numeric()
                                    ->prefix('VNĐ')
                                    ->required(),
                                Select::make('categories')
                                    ->label('Danh mục')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                            ]),
                        RichEditor::make('description')
                            ->label('Mô tả chi tiết')
                            ->columnSpanFull(),
                        FileUpload::make('image_url')
                            ->label('Ảnh sản phẩm')
                            ->image()
                            ->directory('products'),
                        ...(
                            auth()->user()?->hasRole('panel_user')
                                ? [
                                    Textarea::make('stock')
                                        ->label('Nội dung kho (từ file .txt)')
                                        ->placeholder('Mỗi dòng một tài khoản/mã code.')
                                        ->rows(10)
                                        ->columnSpanFull(),
                                ]
                                : []
                        ),
                    ]),
                ...(
                    !auth()->user()?->hasRole('panel_user')
                        ? [
                            Section::make('Quản lý kho & Trạng thái')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('user_id')
                                                ->label('Người bán')
                                                ->relationship('seller', 'username')
                                                ->required()
                                                ->searchable(),
                                            Select::make('status')
                                                ->label('Trạng thái')
                                                ->options([
                                                    'active' => 'Đang bán',
                                                    'sold' => 'Đã bán',
                                                    'deleted' => 'Đã xóa',
                                                    'banned' => 'Bị khóa',
                                                ])
                                                ->default('active')
                                                ->required(),
                                        ]),
                                    Textarea::make('stock')
                                        ->label('Nội dung kho (từ file .txt)')
                                        ->placeholder('Mỗi dòng một tài khoản/mã code.')
                                        ->rows(10)
                                        ->columnSpanFull(),
                                ])
                        ]
                        : []
                )
            ]);
    }
}
