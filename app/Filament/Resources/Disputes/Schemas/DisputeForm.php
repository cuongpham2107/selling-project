<?php

namespace App\Filament\Resources\Disputes\Schemas;

use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DisputeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Thông tin tranh chấp')
                ->description('Chi tiết về lý do và giao dịch bị khiếu nại.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            MorphToSelect::make('transaction')
                                ->label('Giao dịch liên quan')
                                ->types([
                                    MorphToSelect\Type::make(\App\Models\Transaction::class)
                                        ->label('Giao dịch trung gian')
                                        ->titleAttribute('id')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => "Giao dịch trung gian #{$record->id} [".number_format((float) $record->amount).' VNĐ]'),
                                    MorphToSelect\Type::make(\App\Models\ShopTransaction::class)
                                        ->label('Giao dịch gian hàng')
                                        ->titleAttribute('id')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => "Giao dịch gian hàng #{$record->id} [".number_format((float) $record->amount).' VNĐ] - '.($record->product?->name ?? 'Sản phẩm')),
                                ])
                                ->columnSpan(2)
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('initiator_id')
                                ->label('Người khiếu nại')
                                ->placeholder('Chọn')
                                ->relationship('initiator', 'username')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),

                    Placeholder::make('transaction_details')
                        ->label('Liên kết trực tiếp')
                        ->content(function ($get) {
                            $type = $get('transaction_type');
                            $id = $get('transaction_id');

                            if (! $type || ! $id) {
                                return 'Chưa có giao dịch nào được chọn.';
                            }

                            try {
                                if ($type === \App\Models\Transaction::class) {
                                    $url = \App\Filament\Resources\Transactions\TransactionResource::getUrl('view', ['record' => $id]);
                                    $label = "Xem Giao dịch trung gian #$id";
                                } else {
                                    $url = \App\Filament\Resources\ShopTransactions\ShopTransactionResource::getUrl('view', ['record' => $id]);
                                    $label = "Xem Đơn hàng #$id";
                                }

                                return new \Illuminate\Support\HtmlString(
                                    "<a href='{$url}' target='_blank' class='inline-flex items-center gap-1.5 font-bold text-primary-600 hover:text-primary-500 transition-colors underline decoration-2 underline-offset-4'>
                                        <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14'></path></svg>
                                        {$label}
                                    </a>"
                                );
                            } catch (\Exception $e) {
                                return "Giao dịch #$id (Địa chỉ không khả dụng)";
                            }
                        })
                        ->visible(fn ($get) => $get('transaction_id')),
                    Textarea::make('reason')
                        ->label('Lý do tranh chấp')
                        ->required()
                        ->columnSpanFull(),
                ]),
            Section::make('Xử lý & Kết quả')
                ->description('Cập nhật trạng thái và kết quả giải quyết tranh chấp.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('status')
                                ->label('Trạng thái')
                                ->options([
                                    'pending' => 'Chờ xử lý',
                                    'resolving' => 'Đang xử lý',
                                    'resolved' => 'Đã giải quyết',
                                    'cancelled' => 'Đã hủy',
                                ])
                                ->default('pending')
                                ->required(),
                            Select::make('resolved_by')
                                ->label('Người giải quyết')
                                ->relationship('resolver', 'username')
                                ->searchable(),
                        ]),
                    Textarea::make('resolution')
                        ->label('Kết quả giải quyết')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
