<?php

namespace App\Filament\Resources\BalanceTransactions\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                                    ])
                                    ->required(),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Số tiền')
                                    ->required()
                                    ->numeric()
                                    ->prefix('VNĐ')
                                    ->helperText('Số dương = tiền vào, số âm = tiền ra'),
                                TextInput::make('balance_after')
                                    ->label('Số dư sau giao dịch')
                                    ->required()
                                    ->numeric()
                                    ->prefix('VNĐ'),
                                TextInput::make('held_balance_after')
                                    ->label('Số dư giữ sau giao dịch')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('VNĐ'),
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
                                MorphToSelect\Type::make(\App\Models\PointTransaction::class)
                                    ->label('Giao dịch điểm')
                                    ->titleAttribute('id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "Điểm #{$record->id} [{$record->amount} điểm] - {$record->type}"),
                            ])
                            ->searchable()
                            ->preload()
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

