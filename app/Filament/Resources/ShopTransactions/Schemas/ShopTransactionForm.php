<?php

namespace App\Filament\Resources\ShopTransactions\Schemas;

use App\Filament\Resources\ShopTransactions\Enums\Status;
use App\Filament\Resources\ShopTransactions\Fields\ChatField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

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
                        ->columnSpan(2),

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
                                ->description('Thông tin chi tiết về sản phẩm đã mua')
                                ->schema([
                                    Repeater::make('product_data')
                                        ->label('Danh sách sản phẩm/tài khoản')
                                        ->table(function ($record): array {
                                            $type = 'account';
                                            if ($record && ! empty($record->product_data)) {
                                                $firstItem = $record->product_data[0];
                                                if (isset($firstItem['api_key'])) {
                                                    $type = 'api_key';
                                                }
                                            }

                                            if ($type === 'api_key') {
                                                return [
                                                    TableColumn::make('Api Key')->width('250px'),
                                                ];
                                            }

                                            return [
                                                TableColumn::make('Username')->width('150px'),
                                                TableColumn::make('Password')->width('150px'),
                                            ];
                                        })
                                        ->compact()
                                        ->schema(function ($record): array {
                                            $type = 'account';
                                            if ($record && ! empty($record->product_data)) {
                                                $firstItem = $record->product_data[0];
                                                if (isset($firstItem['api_key'])) {
                                                    $type = 'api_key';
                                                }
                                            }

                                            if ($type === 'api_key') {
                                                return [
                                                    TextInput::make('api_key')
                                                        ->label('API Key')
                                                        ->hiddenLabel()
                                                        ->disabled(),
                                                ];
                                            }

                                            if ($type === 'account') {
                                                return [
                                                    TextInput::make('username')
                                                        ->label('Username')
                                                        ->hiddenLabel()
                                                        ->disabled(),
                                                    TextInput::make('password')
                                                        ->label('Password')
                                                        ->hiddenLabel()
                                                        ->disabled(),
                                                ];
                                            }

                                            return [];
                                        })
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->columnSpanFull(),
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
                                        ->dehydrated()
                                        ->columnSpan(2)
                                        ->required(),
                                    TextInput::make('fee')
                                        ->label('Phí (1%)')
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->suffix('VNĐ')
                                        ->disabled()
                                        ->dehydrated()
                                        ->columnSpan(2)
                                        ->required(),
                                    Select::make('status')
                                        ->label('Trạng thái')
                                        ->options(Status::class)
                                        ->default(Status::Pending->value)
                                        // ->disabled()
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
                ])->columns(4)->columnSpanFull(),
            ]);
    }
}
