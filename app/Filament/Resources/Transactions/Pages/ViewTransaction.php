<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Dispute;
use App\Models\PointTier;
use App\Models\PointTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BalanceTransactionService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),

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
                                ->body('Số dư người mua không đủ. Cần '.number_format($totalToHold).' VNĐ (bao gồm phí).')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Hold funds and record transaction
                        BalanceTransactionService::hold(
                            user: $record->buyer,
                            amount: $totalToHold,
                            type: 'hold',
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Giữ tiền cho giao dịch #'.$record->id,
                            metadata: [
                                'amount' => $record->amount,
                                'fee' => $fee,
                                'total' => $totalToHold,
                            ]
                        );

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

            // Người bán đánh dấu hàng hóa là đã gửi
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

            // Người mua đánh dấu hàng hóa là đã nhận và thanh toán
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
                        $totalHeld = $record->amount + $record->fee;

                        // Release held balance from buyer
                        BalanceTransactionService::decrementHeldBalance(
                            user: $record->buyer,
                            amount: $totalHeld,
                            type: 'release',
                            source: $record,
                            relatedUserId: $record->seller_id,
                            description: 'Giải phóng tiền sau khi hoàn tất giao dịch #'.$record->id
                        );

                        // Transfer to seller
                        BalanceTransactionService::incrementBalance(
                            user: $record->seller,
                            amount: $record->amount,
                            type: 'sale',
                            source: $record,
                            relatedUserId: $record->buyer_id,
                            description: 'Thu tiền từ giao dịch #'.$record->id,
                            metadata: [
                                'amount' => $record->amount,
                                'fee_paid_by_buyer' => $record->fee,
                            ]
                        );

                        // Update transaction
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        // Điểm thưởng cho người mua
                        $points = PointTier::calculatePoints((float) $record->amount);
                        if ($points > 0) {
                            $record->buyer->point()->increment('points', $points);

                            PointTransaction::create([
                                'user_id' => $record->buyer_id,
                                'amount' => $points,
                                'type' => 'earn',
                                'related_id' => $record->id,
                                'related_type' => Transaction::class,
                            ]);

                            // Phần thưởng giới thiệu (khớp 100% cho giao dịch đầu tiên)
                            $referrer = $record->buyer->referredBy;
                            if ($referrer) {
                                // Kiểm tra xem đây có phải là giao dịch hoàn thành đầu tiên không
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
                                    // Phần thưởng giới thiệu định kỳ: 10% điểm
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
                ->form([
                    Textarea::make('reason')
                        ->label('Lý do khiếu nại')
                        ->placeholder('Vui lòng mô tả chi tiết vấn đề bạn gặp phải...')
                        ->required()
                        ->rows(5)
                        ->maxLength(1000),
                ])
                ->modalHeading('Tạo khiếu nại / Tranh chấp')
                ->modalDescription('Vui lòng mô tả rõ ràng lý do khiếu nại. Admin sẽ xem xét và xử lý trong thời gian sớm nhất.')
                ->modalSubmitActionLabel('Gửi khiếu nại')
                ->action(function (Transaction $record, array $data) {
                    DB::transaction(function () use ($record, $data) {
                        // Tạo bản ghi Dispute
                        Dispute::create([
                            'transaction_id' => $record->id,
                            'transaction_type' => Transaction::class,
                            'initiator_id' => Auth::id(),
                            'reason' => $data['reason'],
                            'status' => 'pending', // pending, investigating, resolved
                        ]);

                        // Cập nhật trạng thái transaction
                        $record->update(['status' => 'disputed']);
                        // lấy ra tài khoản có quyền là super_admin và support_admin
                        $admins = User::role(['super_admin', 'support_admin'])->get();
                        dd($admins);
                        \Filament\Notifications\Notification::make()
                            ->title('Đã tạo khiếu nại')
                            ->body('Khiếu nại của bạn đã được ghi nhận. Quản trị viên sẽ xem xét và liên hệ trong thời gian sớm nhất.')
                            ->warning()
                            ->send();
                    });
                }),
        ];
    }
}
