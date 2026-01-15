<?php

namespace App\Filament\Resources\Deposits\Schemas;

use App\Filament\Resources\Deposits\Enums\Method;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\RawJs;

class DepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin nạp tiền')
                ->description('Chi tiết về số tiền và phương thức nạp.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('amount')
                                ->label('Số tiền')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->prefix('VNĐ')
                                ->required(),
                            Select::make('method')
                                ->label('Phương thức')
                                ->options(Method::class)
                                ->default(Method::BankTransfer)
                                ->disabled()
                                ->dehydrated()
                                ->required(),
                            Grid::make()->schema([
                                TextEntry::make('bank_account')
                                    ->label('Chủ tài khoản')
                                    ->disabled()
                                    ->weight(FontWeight::Bold)
                                    ->default(fn (): string => config('bank.account') ?? 'Phạm Mạnh Cường'),

                                TextEntry::make('bank_number')
                                    ->label('Số tài khoản')
                                    ->copyable()
                                    ->copyMessage('Copied!')
                                    ->copyMessageDuration(1500)
                                    ->disabled()
                                    ->weight(FontWeight::Bold)
                                    ->default(fn (): string => config('bank.number') ?? '123456789'),
                                TextEntry::make('bank_name')
                                    ->label('Tên ngân hàng')
                                    ->disabled()
                                    ->weight(FontWeight::Bold)
                                    ->default(fn (): string => config('bank.name') ?? 'Ngân hàng ABC'),

                            ])->columns(1),
                            ImageEntry::make('qr_code')
                                ->label('Mã QR')
                                ->disabled()
                                ->width('200px')
                                ->height('200px')
                                ->defaultImageUrl('/images/qr/qr-code.jpg'),

                        ]),
                ])->columnSpanFull(),
            // Section::make('Trạng thái & Người dùng')
            //     ->schema([
            //         Grid::make(2)
            //             ->schema([
            //                 Select::make('user_id')
            //                     ->label('Người dùng')
            //                     ->relationship('user', 'username')
            //                     ->default(auth()->id())
            //                     ->disabled()
            //                     ->dehydrated()
            //                     ->required()
            //                     ->searchable()
            //                     ->preload(),
            //                 Select::make('status')
            //                     ->label('Trạng thái')
            //                     ->options([
            //                         'pending' => 'Chờ duyệt',
            //                         'completed' => 'Thành công',
            //                         'failed' => 'Thất bại',
            //                     ])
            //                     ->default('pending')
            //                     ->required(),
            //             ]),
            //     ])->columnSpanFull(),
        ]);
    }
}
