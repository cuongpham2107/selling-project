<?php

namespace App\Filament\Resources\UserBankAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserBankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin tài khoản ngân hàng')
                    ->description('Thêm thông tin tài khoản ngân hàng để rút tiền')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('user_id')
                            ->label('Người dùng')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hidden(fn () => ! auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                            ->default(fn () => auth()->id()),

                        Select::make('bank_name')
                            ->label('Ngân hàng')
                            ->options([
                                'Vietcombank' => 'Vietcombank - Ngân hàng TMCP Ngoại thương Việt Nam',
                                'VietinBank' => 'VietinBank - Ngân hàng TMCP Công Thương Việt Nam',
                                'BIDV' => 'BIDV - Ngân hàng TMCP Đầu tư và Phát triển Việt Nam',
                                'Agribank' => 'Agribank - Ngân hàng Nông nghiệp và Phát triển Nông thôn',
                                'ACB' => 'ACB - Ngân hàng TMCP Á Châu',
                                'Techcombank' => 'Techcombank - Ngân hàng TMCP Kỹ Thương Việt Nam',
                                'MB Bank' => 'MB Bank - Ngân hàng TMCP Quân đội',
                                'VPBank' => 'VPBank - Ngân hàng TMCP Việt Nam Thịnh Vượng',
                                'TPBank' => 'TPBank - Ngân hàng TMCP Tiên Phong',
                                'Sacombank' => 'Sacombank - Ngân hàng TMCP Sài Gòn Thương Tín',
                                'HDBank' => 'HDBank - Ngân hàng TMCP Phát triển TP.HCM',
                                'SHB' => 'SHB - Ngân hàng TMCP Sài Gòn - Hà Nội',
                                'VIB' => 'VIB - Ngân hàng TMCP Quốc tế',
                                'MSB' => 'MSB - Ngân hàng TMCP Hàng Hải',
                                'OCB' => 'OCB - Ngân hàng TMCP Phương Đông',
                                'SeABank' => 'SeABank - Ngân hàng TMCP Đông Nam Á',
                                'VietCapitalBank' => 'VietCapitalBank - Ngân hàng TMCP Bản Việt',
                                'SCB' => 'SCB - Ngân hàng TMCP Sài Gòn',
                                'VietABank' => 'VietABank - Ngân hàng TMCP Việt Á',
                                'Nam A Bank' => 'Nam A Bank - Ngân hàng TMCP Nam Á',
                                'PG Bank' => 'PG Bank - Ngân hàng TMCP Xăng dầu Petrolimex',
                                'BacA Bank' => 'BacA Bank - Ngân hàng TMCP Bắc Á',
                                'CAKE by VPBank' => 'CAKE by VPBank',
                                'Timo' => 'Timo by Ban Viet Bank',
                            ])
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('account_holder_name')
                            ->label('Tên chủ tài khoản')
                            ->placeholder('Nhập tên chủ tài khoản (viết hoa, không dấu)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('account_number')
                            ->label('Số tài khoản')
                            ->placeholder('Nhập số tài khoản ngân hàng')
                            ->required()
                            ->numeric()
                            ->maxLength(50)
                            ->columnSpanFull(),

                        Toggle::make('is_default')
                            ->label('Đặt làm tài khoản mặc định')
                            ->helperText('Tài khoản mặc định sẽ được sử dụng cho các giao dịch rút tiền')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
