<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin cơ bản')
                    ->description('Thông tin đăng nhập và liên hệ của người dùng.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('username')
                                    ->label('Tên đăng nhập')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                TextInput::make('password')
                                    ->label('Mật khẩu')
                                    ->password()
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->dehydrated(fn ($state) => filled($state)),
                                TextInput::make('phone')
                                    ->label('Số điện thoại')
                                    ->tel(),
                            ]),
                    ]),
                Section::make('Tài chính')
                    ->description('Thông tin tài chính của người dùng.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('initial_balance')
                                    ->label('Số dư')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                        if ($record && $record->balance) {
                                            $component->state($record->balance->balance);
                                        }
                                    }),
                                TextInput::make('initial_held_balance')
                                    ->label('Số dư bị giữ')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                        if ($record && $record->balance) {
                                            $component->state($record->balance->held_balance ?? 0);
                                        }
                                    }),
                            ]),
                    ]),
                Section::make('Phân quyền & Giới thiệu')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->disabled(fn () => ! auth()->user()->hasRole(config('filament-shield.super_admin.name'))),
                                TextInput::make('referral_code')
                                    ->label('Mã giới thiệu')
                                    ->unique(ignoreRecord: true),
                                Select::make('referred_by')
                                    ->label('Người giới thiệu')
                                    ->relationship('referredBy', 'username')
                                    ->searchable(),
                            ]),
                    ]),
                Section::make('Xác minh (KYC)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('kyc_status')
                                    ->label('Trạng thái KYC')
                                    ->options([
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                    ])
                                    ->columnSpanFull()
                                    ->default('pending')
                                    ->disabled(fn () => ! auth()->user()->hasRole(config('filament-shield.super_admin.name'))),
                                FileUpload::make('kyc_documents')
                                    ->label('Giấy tờ xác minh')
                                    ->multiple()
                                    ->directory('kyc-documents')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
