<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\PointTransaction;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            // Partner confirms the transaction request
            Action::make('confirm')
                ->label('Xác nhận giao dịch')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Transaction $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function (Transaction $record) {
                    DB::transaction(function () use ($record) {
                        $buyerBalance = $record->buyer->balance;
                        
                        // Calculate fee (Buyer pays)
                        $fee = $record->calculateTotalFee();
                        $totalToHold = $record->amount + $fee;

                        if ($buyerBalance->balance < $totalToHold) {
                            \Filament\Notifications\Notification::make()
                                ->title('Số dư không đủ')
                                ->body("Số dư người mua không đủ. Cần " . number_format($totalToHold) . " VNĐ (bao gồm phí).")
                                ->danger()
                                ->send();

                            return;
                        }

                        // Hold funds
                        $buyerBalance->decrement('balance', $totalToHold);
                        $buyerBalance->increment('held_balance', $totalToHold);

                        $record->update([
                            'status' => 'confirmed',
                            'fee' => $fee,
                            'confirmed_at' => now(),
                            'end_time' => now()->addHours($record->duration),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Giao dịch đã xác nhận')
                            ->body('Hệ thống đã tạm giữ số tiền và phí từ người mua.')
                            ->success()
                            ->send();
                    });
                }),

            // Seller marks the goods as sent
            Action::make('seller_sent')
                ->label('Đã gửi hàng')
                ->color('warning')
                ->icon('heroicon-o-truck')
                ->visible(fn (Transaction $record) => $record->status === 'confirmed')
                ->requiresConfirmation()
                ->action(function (Transaction $record) {
                    $record->update(['status' => 'seller_sent']);

                    \Filament\Notifications\Notification::make()
                        ->title('Trạng thái cập nhật')
                        ->body('Chờ người mua xác nhận đã nhận hàng.')
                        ->info()
                        ->send();
                }),

            // Buyer marks the goods as received and releases payment
            Action::make('buyer_received')
                ->label('Đã nhận hàng')
                ->color('success')
                ->icon('heroicon-o-hand-thumb-up')
                ->visible(fn (Transaction $record) => in_array($record->status, ['confirmed', 'seller_sent']))
                ->requiresConfirmation()
                ->modalHeading('Hoàn tất giao dịch')
                ->modalDescription('Bạn chắc chắn đã nhận hàng? Tiền sẽ được chuyển cho người bán ngay lập tức.')
                ->action(function (Transaction $record) {
                    DB::transaction(function () use ($record) {
                        $buyerBalance = $record->buyer->balance;
                        $sellerBalance = $record->seller->balance;
                        $totalHeld = $record->amount + $record->fee;

                        // Release from held balance
                        $buyerBalance->decrement('held_balance', $totalHeld);

                        // Transfer to seller (Buyer paid the fee on top, so seller gets full amount)
                        $sellerBalance->increment('balance', $record->amount);

                        // Update transaction
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        // Award Points to buyer
                        $points = Transaction::calculatePoints($record->amount);
                        if ($points > 0) {
                            $record->buyer->point()->increment('points', $points);

                            PointTransaction::create([
                                'user_id' => $record->buyer_id,
                                'amount' => $points,
                                'type' => 'earn',
                                'related_id' => $record->id,
                                'related_type' => Transaction::class,
                            ]);

                            // Referral reward (100% matched for first transaction)
                            $referrer = $record->buyer->referredBy;
                            if ($referrer) {
                                // Check if this is the first completed transaction
                                $previousCount = Transaction::where('buyer_id', $record->buyer_id)
                                    ->where('status', 'completed')
                                    ->where('id', '!=', $record->id)
                                    ->count();

                                if ($previousCount === 0) {
                                    $referrer->point()->increment('points', $points);
                                    PointTransaction::create([
                                        'user_id' => $referrer->id,
                                        'amount' => $points,
                                        'type' => 'earn',
                                        'related_id' => $record->id,
                                        'related_type' => Transaction::class,
                                        'recipient_id' => $record->buyer_id, // Who they got it from
                                    ]);
                                } else {
                                    // Recurring referral reward: 10% of points
                                    $recurringPoints = floor($points * 0.1);
                                    if ($recurringPoints > 0) {
                                        $referrer->point()->increment('points', $recurringPoints);
                                        PointTransaction::create([
                                            'user_id' => $referrer->id,
                                            'amount' => $recurringPoints,
                                            'type' => 'earn',
                                            'related_id' => $record->id,
                                            'related_type' => Transaction::class,
                                            'recipient_id' => $record->buyer_id,
                                        ]);
                                    }
                                }
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Giao dịch hoàn tất')
                            ->body('Tiền đã được chuyển cho người bán và điểm thưởng đã được cộng.')
                            ->success()
                            ->send();
                    });
                }),

            // Initiate dispute
            Action::make('dispute')
                ->label('Khiếu nại / Tranh chấp')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle')
                ->visible(fn (Transaction $record) => in_array($record->status, ['confirmed', 'seller_sent', 'buyer_received']))
                ->requiresConfirmation()
                ->action(function (Transaction $record) {
                    $record->update(['status' => 'disputed']);

                    \Filament\Notifications\Notification::make()
                        ->title('Đã mở tranh chấp')
                        ->body('Quản trị viên sẽ xem xét và xử lý.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}
