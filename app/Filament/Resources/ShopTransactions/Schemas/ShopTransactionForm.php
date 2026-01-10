<?php

namespace App\Filament\Resources\ShopTransactions\Schemas;

use App\Filament\Resources\ShopTransactions\Enums\Status;
use App\Filament\Resources\ShopTransactions\Fields\ChatField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Schemas\Components\Icon;
use Filament\Support\Icons\Heroicon;

class ShopTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()->schema([
                    // Phần 1: Chiếm 2 cột (Chat)
                    Section::make('Chat giao dịch')
                        ->schema([
                            ChatField::make('chat_id')
                                ->label('Phòng chat hỗ trợ'),
                        ])
                        ->columnSpan(3),

                    // Phần 2: Chiếm 1 cột (Thông tin & Trạng thái) - Xếp hàng dọc
                    Grid::make(1)
                        ->schema([
                            Section::make('Thông tin đơn hàng')
                                ->columns(2)
                                ->schema([
                                    Select::make('buyer_id')
                                        ->label('Người mua')
                                        ->relationship('buyer', 'username')
                                        ->required()
                                        ->disabled()
                                        ->searchable(),
                                    Select::make('seller_id')
                                        ->label('Người bán')
                                        ->relationship('seller', 'username')
                                        ->required()
                                        ->disabled()
                                        ->searchable(),
                                    Select::make('product_id')
                                        ->label('Sản phẩm')
                                        ->relationship('product', 'name')
                                        ->required()
                                        ->disabled()
                                        ->columnSpanFull()
                                        ->searchable(),
                                ]),

                            Section::make('Nội dung sản phẩm')
                                ->description('Thông tin chi tiết về sản phẩm (chỉ hiển thị khi đơn hàng được xác nhận)')
                                ->schema([
                                    Textarea::make('product_stock')
                                        ->label('Nội dung sản phẩm')
                                        ->rows(10)
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('Nội dung sản phẩm sẽ hiển thị khi người bán xác nhận đơn hàng')
                                        ->afterStateHydrated(function (Textarea $component, $record) {
                                            if ($record && $record->product) {
                                                // Explicitly load stock using getAttributes to bypass hidden
                                                $stock = $record->product->getAttributes()['stock'] ?? null;
                                                $component->state($stock);
                                            }
                                        }),
                                ])
                                ->visible(fn ($record) => $record &&
                                    in_array($record->status, [Status::Held, Status::Completed, Status::Disputed])
                                ),

                            Section::make('Tài chính & Trạng thái')
                                ->columns(6)
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('Số tiền')
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->suffix('VNĐ')
                                        ->disabled()
                                        ->columnSpan(2)
                                        ->required(),
                                    TextInput::make('fee')
                                        ->label('Phí (1%)')
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->suffix('VNĐ')
                                        ->disabled()
                                        ->columnSpan(2)
                                        ->required(),
                                    Select::make('status')
                                        ->label('Trạng thái')
                                        ->enum(Status::class)
                                        ->default(Status::Pending->value)
                                        ->disabled()
                                        ->columnSpan(2)
                                        ->required(),
                                    DateTimePicker::make('end_time')
                                        ->disabled()
                                        ->columnSpan(3)
                                        ->label('Hạn tự động hoàn thành')
                                        ->belowContent('Tự động hoàn thành sau 3 ngày nếu không có hành động nào'),
                                    DateTimePicker::make('completed_at')
                                        ->disabled()
                                        ->columnSpan(3)
                                        ->label('Hoàn thành lúc'),
                                ]),
                        ])
                        ->columnSpan(2),
                ])->columns(5)->columnSpanFull(),
            ]);
    }
}
