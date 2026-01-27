<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use App\Filament\Resources\Withdrawals\Enums\Method;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class WithdrawalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin rút tiền')
                ->description('Chi tiết về số tiền và phương thức nhận tiền.')
                ->columnSpanFull()
                ->schema([
                    Placeholder::make('current_balance')
                        ->label('Số dư khả dụng')
                        ->content(function () {
                            $balance = auth()->user()->balance?->balance ?? 0;

                            return number_format((float) $balance, 0, ',', '.').' vnđ';
                        })
                        ->extraAttributes(['class' => 'text-lg font-bold text-success-600'])
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('amount')
                                ->label('Số tiền')
                                ->numeric()
                                ->prefix('VNĐ')
                                ->required()
                                ->helperText('Nhập số tiền bạn muốn rút từ ví của mình.'),
                            Select::make('type')
                                ->label('Phương thức')
                                ->options(Method::class)
                                ->default(Method::BankTransfer)
                                ->disabled()
                                ->dehydrated()
                                ->required(),
                            // Select::make('status')
                            //     ->label('Trạng thái')
                            //     ->options([
                            //         'pending' => 'Chờ duyệt',
                            //         'completed' => 'Thành công',
                            //         'failed' => 'Thất bại',
                            //     ])
                            //     ->default('pending')
                            //     ->required(),
                        ]),
                ]),
            Section::make('Thông tin tài khoản')
                ->description('Chi tiết về tài khoản ngân hàng.')
                ->schema([
                    Grid::make(1)
                        ->schema([
                            TextEntry::make('user_id')
                                ->label('Tài khoản người dùng')
                                ->default(fn () => auth()->user()->username)
                                ->disabled()
                                ->weight(FontWeight::Bold),
                            TextEntry::make('account_holder_name')
                                ->label('Chủ tài khoản')
                                ->default(fn () => auth()->user()->defaultBankAccount?->account_holder_name ?? '')
                                ->weight(FontWeight::Bold)
                                ->disabled(),
                            TextEntry::make('account_number')
                                ->label('Số tài khoản')
                                ->default(fn () => auth()->user()->defaultBankAccount?->account_number ?? '')
                                ->weight(FontWeight::Bold)
                                ->disabled(),
                            TextEntry::make('bank_name')
                                ->label('Tên ngân hàng')
                                ->default(fn () => auth()->user()->defaultBankAccount?->bank_name ?? '')
                                ->weight(FontWeight::Bold)
                                ->disabled(),
                        ]),
                ]),
        ]);
    }
}
