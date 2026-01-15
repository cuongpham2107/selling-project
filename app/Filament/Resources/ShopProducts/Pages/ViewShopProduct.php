<?php

namespace App\Filament\Resources\ShopProducts\Pages;

use App\Filament\Resources\ShopProducts\ShopProductResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ViewShopProduct extends ViewRecord
{
    protected static string $resource = ShopProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Thông tin sản phẩm')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Group::make([
                                    ImageEntry::make('image_url')
                                        ->label('Hình ảnh')
                                        ->disk('public')
                                        ->height(400)
                                        ->defaultImageUrl(url('/images/placeholder.png')),
                                ])->columnSpan(1),

                                Group::make([
                                    TextEntry::make('name')
                                        ->label('Tên sản phẩm')
                                        ->size(TextSize::Large)
                                        ->weight(FontWeight::Bold),

                                    TextEntry::make('description')
                                        ->label('Mô tả')
                                        ->markdown()
                                        ->default('Không có mô tả cho sản phẩm.'),

                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('price')
                                                ->label('Giá')
                                                ->money('VND')
                                                ->size(TextSize::Large)
                                                ->weight(FontWeight::Bold)
                                                ->color('primary'),

                                            TextEntry::make('stock')
                                                ->label('Số lượng còn lại')
                                                ->badge()
                                                ->color(fn ($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                                                ->visible(fn () => auth()->user()->hasRole(config('filament-shield.super_admin.name')) || $this->record->user_id === auth()->id()),
                                        ]),

                                    TextEntry::make('status')
                                        ->label('Trạng thái')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'active' => 'success',
                                            'sold' => 'gray',
                                            'deleted' => 'danger',
                                            'banned' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'active' => 'Đang hoạt động',
                                            'sold' => 'Đã bán',
                                            'deleted' => 'Đã xóa',
                                            'banned' => 'Đã khóa',
                                            default => $state,
                                        }),

                                    TextEntry::make('categories.name')
                                        ->label('Danh mục')
                                        ->badge()
                                        ->separator(',')
                                        ->color('info'),
                                ])->columnSpan(2),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Thông tin người bán')
                    ->schema([
                        TextEntry::make('seller.username')
                            ->label('Tên người bán')
                            ->icon('heroicon-s-user')
                            ->iconColor('primary'),

                        TextEntry::make('seller.email')
                            ->label('Email')
                            ->icon('heroicon-s-envelope')
                            ->copyable()
                            ->copyMessage('Đã sao chép email!')
                            ->copyMessageDuration(1500),

                        TextEntry::make('created_at')
                            ->label('Ngày đăng')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-s-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Cập nhật lần cuối')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-s-clock'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Lịch sử giao dịch')
                    ->schema([
                        TextEntry::make('transactions')
                            ->label('Số lượng giao dịch')
                            ->state(fn ($record) => $record->transactions()->count())
                            ->badge()
                            ->color('success'),
                    ])
                    ->visible(fn () => auth()->user()->hasRole(config('filament-shield.super_admin.name')) || $this->record->user_id === auth()->id())
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
