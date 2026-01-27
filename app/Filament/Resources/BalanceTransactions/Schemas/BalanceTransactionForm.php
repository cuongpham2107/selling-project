<?php

namespace App\Filament\Resources\BalanceTransactions\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class BalanceTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin giao dịch')
                    ->description('Chi tiết về giao dịch tiền của người dùng.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Người dùng')
                                    ->relationship('user', 'username')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('type')
                                    ->label('Loại giao dịch')
                                    ->options([
                                        'deposit' => 'Nạp tiền',
                                        'withdrawal' => 'Rút tiền',
                                        'purchase' => 'Mua hàng',
                                        'sale' => 'Bán hàng',
                                        'hold' => 'Giữ tiền',
                                        'release' => 'Giải phóng tiền',
                                        'refund' => 'Hoàn tiền',
                                        'point_redeem' => 'Đổi điểm',
                                        'fee' => 'Phí giao dịch',
                                        'dispute_refund' => 'Hoàn tiền tranh chấp',
                                        'dispute_payout' => 'Thanh toán tranh chấp',
                                        'middleman_purchase' => 'Mua qua trung gian',
                                        'middleman_sale' => 'Bán qua trung gian',
                                        'point_earn' => 'Kiếm điểm',
                                        'point_send' => 'Gửi điểm',
                                        'point_receive' => 'Nhận điểm',
                                        'redeem' => 'Quy đổi',
                                    ])
                                    ->required(),
                                Select::make('currency')
                                    ->label('Tiền tệ')
                                    ->options([
                                        'vnd' => 'vnđ',
                                        'point' => 'điểm',
                                    ])
                                    ->required()
                                    ->default('vnd'),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('amount')
                                    ->label(fn ($record) => $record?->currency === 'point' ? 'Số điểm' : 'Số tiền')
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->suffix(fn ($record) => $record?->currency === 'point' ? 'điểm' : 'vnđ')
                                    ->helperText(fn ($record) => $record?->currency === 'point' ? 'Số dương = điểm vào, số âm = điểm ra' : 'Số dương = tiền vào, số âm = tiền ra'),
                                TextInput::make('balance_after')
                                    ->label('Số tiền trong ví')
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->suffix('vnđ'),
                                TextInput::make('held_balance_after')
                                    ->label('Số tiền đang bị tạm giữ')
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('vnđ'),
                                TextInput::make('points_after')
                                    ->label('Số dư điểm')
                                    ->required()
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('điểm'),
                            ]),

                        Select::make('related_user_id')
                            ->label('Người dùng liên quan (Người mua/bán)')
                            ->relationship('relatedUser', 'username')
                            ->searchable()
                            ->preload(),

                        Textarea::make('description')
                            ->label('Mô tả')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Nguồn giao dịch')
                    ->description('Giao dịch gốc tạo ra thay đổi số dư này.')
                    ->schema([
                        MorphToSelect::make('source')
                            ->label('Liên kết giao dịch')
                            ->types([
                                MorphToSelect\Type::make(\App\Models\Deposit::class)
                                    ->label('Nạp tiền')
                                    ->titleAttribute('id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "Nạp tiền #{$record->id} [".number_format((float) $record->amount, 0, ',', '.').' VNĐ]'),
                                MorphToSelect\Type::make(\App\Models\Withdrawal::class)
                                    ->label('Rút tiền')
                                    ->titleAttribute('id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "Rút tiền #{$record->id} [".number_format((float) $record->amount, 0, ',', '.').' VNĐ]'),
                                MorphToSelect\Type::make(\App\Models\ShopTransaction::class)
                                    ->label('Giao dịch cửa hàng')
                                    ->titleAttribute('id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "Đơn hàng #{$record->id} [".number_format((float) $record->amount, 0, ',', '.').' VNĐ] - '.($record->product?->name ?? 'Sản phẩm')),
                                MorphToSelect\Type::make(\App\Models\Transaction::class)
                                    ->label('Giao dịch trung gian')
                                    ->titleAttribute('id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "Giao dịch #{$record->id} [".number_format((float) $record->amount, 0, ',', '.').' VNĐ]'),
                            ])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->columnSpanFull(),

                        Placeholder::make('source_link')
                            ->label('Xem giao dịch')
                            ->content(function ($record) {
                                if (! $record || ! $record->source_type || ! $record->source_id) {
                                    return '-';
                                }

                                $url = match ($record->source_type) {
                                    'App\\Models\\Deposit' => route('filament.admin.resources.deposits.edit', $record->source_id),
                                    'App\\Models\\Withdrawal' => route('filament.admin.resources.withdrawals.index'),
                                    'App\\Models\\ShopTransaction' => route('filament.admin.resources.shop-transactions.view', $record->source_id),
                                    'App\\Models\\Transaction' => route('filament.admin.resources.transactions.edit', $record->source_id),
                                    default => null,
                                };

                                if (! $url) {
                                    return '-';
                                }

                                return new \Illuminate\Support\HtmlString(
                                    '<a href="'.$url.'" class="text-primary-600 hover:underline" target="_blank">Xem chi tiết giao dịch</a>'
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Metadata')
                    ->description('Thông tin bổ sung (JSON)')
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Dữ liệu bổ sung')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
