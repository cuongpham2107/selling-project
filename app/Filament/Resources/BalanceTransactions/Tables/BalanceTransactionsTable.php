<?php

namespace App\Filament\Resources\BalanceTransactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BalanceTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->color(fn (string $state): string => match ($state) {
                        'deposit', 'sale', 'release', 'refund', 'dispute_refund', 'point_redeem' => 'success',
                        'withdrawal', 'purchase', 'hold', 'fee' => 'warning',
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
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Số tiền')
                    ->money('VND')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ($state >= 0 ? '+' : '') . number_format((float) $state, 0, ',', '.') . ' VNĐ')
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->label('Số dư sau')
                    ->money('VND')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('held_balance_after')
                    ->label('Số dư giữ sau')
                    ->money('VND')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source_type')
                    ->label('Nguồn')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'App\\Models\\Deposit' => 'Nạp tiền',
                        'App\\Models\\Withdrawal' => 'Rút tiền',
                        'App\\Models\\ShopTransaction' => 'Đơn hàng',
                        'App\\Models\\Transaction' => 'Trung gian',
                        'App\\Models\\PointTransaction' => 'Điểm',
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
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
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
                    ])
                    ->multiple(),
                SelectFilter::make('user')
                    ->label('Người dùng')
                    ->relationship('user', 'username')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
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

