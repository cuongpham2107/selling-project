<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

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
                        Grid::make(3)
                            ->schema([
                                    TextEntry::make('balance.balance')
                                        ->label('Số dư')
                                        ->money('VND')
                                        ->icon('heroicon-o-currency-dollar')
                                        ->weight(FontWeight::Bold)
                                        ->color('success')
                                        ->default(0),
                                    TextEntry::make('balance.held_balance')
                                        ->label('Số dư bị giữ')
                                        ->money('VND')
                                        ->icon('heroicon-o-currency-dollar')
                                        ->weight(FontWeight::Bold)
                                        ->color('warning')
                                        ->default(0),
                                    //Point
                                    TextEntry::make('point.points')
                                        ->label('Điểm')
                                        ->icon('heroicon-o-star')
                                        ->weight(FontWeight::Bold)
                                        ->color('primary')
                                        ->default(0),
                            ]),
                    ]),
                Section::make('Phân quyền & Giới thiệu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull()
                                    ->hidden(fn () => ! auth()->user()->hasRole(config('filament-shield.super_admin.name'))),
                                TextInput::make('referral_code')
                                    ->label('Mã giới thiệu')
                                    ->disabled(fn () => ! auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                                    ->dehydrated(fn ($state) => $state !== null)
                                    ->unique(ignoreRecord: true),
                                Select::make('referred_by')
                                    ->label('Người giới thiệu')
                                    ->relationship('referredBy', 'username')
                                    ->disabled(fn () => ! auth()->user()->hasRole(config('filament-shield.super_admin.name')))
                                    ->dehydrated(fn ($state) => $state !== null)
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
