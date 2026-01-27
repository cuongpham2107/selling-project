<?php

namespace App\Filament\Resources\ShopProducts\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ShopProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin sản phẩm')
                    ->schema([
                        Grid::make(3)
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
                                ? static::getStockComponent()
                                : []
                        ),
                    ])->columnSpanFull(),
                ...(
                    ! auth()->user()?->hasRole('panel_user')
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
                                    ...static::getStockComponent(),
                                ]),
                        ]
                        : []
                ),
            ]);
    }

    protected static function getStockComponent()
    {

        return [
            Radio::make('type')
                ->label('Loại sản phẩm')
                ->options([
                    'api_key' => 'Api Key',
                    'account' => 'Tài khoản',
                    // 'code' => 'Mã code',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                    $type = $get('type');
                    if ($type === 'api_key') {
                        $set('stock', [
                            [
                                'api_key' => '',
                            ],
                        ]);
                    }
                    if ($type === 'account') {
                        $set('stock', [
                            [
                                'username' => '',
                                'password' => '',
                            ],
                        ]);
                    }
                })
                ->dehydrated()
                ->default('api_key'),
            Repeater::make('stock')
                ->label('Kho')
                ->table(function (Get $get): array {
                    $type = $get('type');
                    $columns = [];

                    if ($type === 'api_key') {
                        $columns[] = TableColumn::make('Api Key');
                    }

                    if ($type === 'account') {
                        $columns[] = TableColumn::make('Username');
                        $columns[] = TableColumn::make('Password');
                    }

                    return $columns;
                })
                ->schema(function (Get $get): array {
                    $type = $get('type');

                    if ($type === 'api_key') {
                        return [
                            TextInput::make('api_key')
                                ->required(),
                        ];
                    }

                    if ($type === 'account') {
                        return [
                            TextInput::make('username')
                                ->required(),
                            TextInput::make('password')
                                ->required(),
                        ];
                    }

                    return [];
                })
                ->addActionLabel('Thêm')
                ->afterLabel([
                    Action::make('import_file_excel')
                        ->label('Import file excel')
                        ->icon('heroicon-s-document')
                        ->modal()
                        ->modalHeading('Import file excel')
                        ->modalDescription('Tải file excel để import kho')
                        ->form([
                            Text::make(new HtmlString('<strong>File mẫu:</strong> <a href="">Download</a>')),
                            FileUpload::make('file')
                                ->label('File excel')
                                ->required()
                                ->directory('products'),
                        ])
                        ->action(function () {}),
                ])
                ->columnSpanFull(),
        ];
    }
}
