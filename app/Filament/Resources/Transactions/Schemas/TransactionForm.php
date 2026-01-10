<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Filament\Resources\Transactions\Fields\ChatField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Schemas\Components\Icon;

class TransactionForm
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
                            Section::make('Thông tin giao dịch')
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
                                    Textarea::make('description')
                                        ->label('Mô tả giao dịch')
                                        ->rows(3)
                                        ->disabled()
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Tài chính & Trạng thái')
                                ->columns(6)
                                ->schema([
                                    TextInput::make('amount')
                                        ->label('Số tiền')
                                        ->numeric()
                                        ->mask(RawJs::make('$money($input)'))
                                        ->suffix('VNĐ')
                                        ->disabled()
                                        ->required()
                                        ->columnSpan(3),
                                    TextInput::make('fee')
                                        ->label('Phí giao dịch')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->numeric()
                                        ->suffix('VNĐ')
                                        ->disabled()
                                        ->required()
                                        ->columnSpan(3),
                                    TextInput::make('duration')
                                        ->label('Thời gian')
                                        ->numeric()
                                        ->disabled()
                                        ->prefixIcon(Heroicon::Clock)
                                        ->suffix('Giờ')
                                        ->required()
                                        ->columnSpan(3),
                                   
                                    Select::make('status')
                                        ->label('Trạng thái')
                                        ->options([
                                            'pending' => 'Chờ xác nhận',
                                            'confirmed' => 'Đã xác nhận',
                                            'seller_sent' => 'Người bán đã gửi',
                                            'buyer_received' => 'Người mua đã nhận',
                                            'completed' => 'Hoàn thành',
                                            'disputed' => 'Tranh chấp',
                                            'cancelled' => 'Đã hủy',
                                            'overdue' => 'Quá hạn',
                                        ])
                                        ->default('pending')
                                        ->disabled()
                                        ->required()
                                        ->columnSpan(3),
                                    DateTimePicker::make('confirmed_at')
                                        ->disabled()
                                        ->label('Xác nhận lúc')
                                        // ->prefixIcon(Heroicon::CalendarDays)
                                        ->displayFormat('d/m/Y H:i')
                                        ->seconds(false)
                                        ->columnSpan(2),
                                    DateTimePicker::make('end_time')
                                        ->disabled()
                                        ->label('Thời hạn kết thúc')
                                        ->displayFormat('d/m/Y H:i')
                                        ->seconds(false)
                                        ->columnSpan(2),
                                    DateTimePicker::make('completed_at')
                                        ->disabled()
                                        ->label('Hoàn thành lúc')
                                        // ->prefixIcon(Heroicon::CalendarDays)
                                        ->displayFormat('d/m/Y H:i')
                                        ->seconds(false)
                                        ->columnSpan(2),
                                ]),
                        ])
                        ->columnSpan(2),
                ])->columns(5)->columnSpanFull(),
            ]);
    }
}
