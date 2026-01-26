<?php

namespace App\Filament\Resources\BalanceTransactions\Tables;

use App\Filament\Tables\BaseTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BalanceTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return BaseTable::configure($table)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.username')
                    ->label('Người dùng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại')
                    ->badge()
                    ->color(fn ($record): string => match ($record->type) {
                        'deposit', 'sale', 'release', 'refund', 'dispute_refund', 'point_redeem', 'point_earn', 'point_receive' => 'success',
                        'withdrawal', 'purchase', 'hold', 'fee', 'point_send', 'redeem' => 'warning',
                        'middleman_purchase' => 'info',
                        'middleman_sale' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
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
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Số tiền')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($record) => $record->amount_formatted)
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('Tổng VNĐ')
                            ->query(fn ($query) => $query->where('currency', 'vnd'))
                            ->numeric(
                                decimalPlaces: 0,
                                decimalSeparator: ',',
                                thousandsSeparator: '.',
                            )
                            ->suffix(' vnđ'),
                        Sum::make()
                            ->label('Tổng Điểm')
                            ->query(fn ($query) => $query->where('currency', 'point'))
                            ->numeric(
                                decimalPlaces: 2,
                                decimalSeparator: ',',
                                thousandsSeparator: '.',
                            )
                            ->suffix(' điểm'),
                    ]),
                TextColumn::make('currency')
                    ->label('Tiền tệ')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('balance_after')
                    ->label('Số dư vnđ sau')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.').' vnđ')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('held_balance_after')
                    ->label('Treo vnđ sau')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.').' vnđ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('points_after')
                    ->label('Số dư điểm sau')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.').' điểm')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('source_type')
                    ->label('Nguồn')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'App\\Models\\Deposit' => 'Nạp tiền',
                        'App\\Models\\Withdrawal' => 'Rút tiền',
                        'App\\Models\\ShopTransaction' => 'Đơn hàng',
                        'App\\Models\\Transaction' => 'Trung gian',
                        default => '-',
                    })
                    ->toggleable(),
                TextColumn::make('relatedUser.username')
                    ->label('Người liên quan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('Mô tả')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(),
                BaseTable::getCreatedAtColumn(),
            ])
            ->filters([
                SelectFilter::make('type')
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
                    ->multiple(),
                SelectFilter::make('currency')
                    ->label('Tiền tệ')
                    ->options([
                        'vnd' => 'VNĐ',
                        'point' => 'Point',
                    ]),
                BaseTable::getUserFilter(),
                BaseTable::getCreatedAtFilter(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
